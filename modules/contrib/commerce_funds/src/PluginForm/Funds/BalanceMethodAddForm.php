<?php

namespace Drupal\commerce_funds\PluginForm\Funds;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\commerce_payment\Exception\DeclineException;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentGatewayFormBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\profile\Entity\Profile;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines Balance payment Method add form.
 */
class BalanceMethodAddForm extends PaymentGatewayFormBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The route.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Constructs a new PaymentMethodAddForm.
   */
  public function __construct(RouteMatchInterface $route_match, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $account) {
    $this->routeMatch = $route_match;
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('logger.factory')->get('commerce_payment'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method */
    $payment_method = $this->entity;
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $this->routeMatch->getParameter('commerce_order');
    // Payment-method/add form.
    if (!$order) {
      $currencies = $this->entityTypeManager->getStorage('commerce_currency')->loadMultiple();
      $currencyCodes = [];
      foreach ($currencies as $currency) {
        $currency_code = $currency->getCurrencyCode();
        $currencyCodes[$currency_code] = $currency_code;
      }

      $form['currency'] = [
        '#type' => 'select',
        '#title' => $this->t('Create a virtual wallet'),
        '#description' => $this->t('Select the currency.'),
        '#options' => $currencyCodes,
      ];
    }
    // Checkout form.
    else {
      $form['#attached']['library'][] = 'commerce_payment/payment_method_form';
      $form['#tree'] = TRUE;

      $balance = \Drupal::service('commerce_funds.transaction_manager')->loadAccountBalance($payment_method->getOwner());
      $balance_currency = isset($balance[$order->getTotalPrice()->getCurrencyCode()]) ? $balance[$order->getTotalPrice()->getCurrencyCode()] : NULL;

      $funds = $this->t('Selecting this method will create a new virtual wallet for @currency currency.', [
        '@currency' => $order->getTotalPrice()->getCurrencyCode(),
      ]);
      $no_funds = $this->t('You don\'t have funds in this currency in your balance. Please <a href="@url">make a deposit</a> first.', [
        '@url' => Url::fromRoute('commerce_funds.deposit')->toString(),
      ]);
      $balance_msg = $balance_currency ? $funds : $no_funds;

      $form['payment_details'] = [
        '#type' => 'container',
        '#payment_method_type' => $payment_method->bundle(),
        '#markup' => $balance_msg,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $this->routeMatch->getParameter('commerce_order');
    if (!$order) {
      $currency = $form_state->getValue('currency');
      /** @var \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method */
      $payment_method = $this->loadPaymentMethod($this->account->id(), $currency);
      $field = $form['currency'];
    }
    else {
      /** @var \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method */
      $payment_method = $this->loadPaymentMethod($order->getCustomerId(), $order->getTotalPrice()->getCurrencyCode());
      if ($payment_method) {
        $currency = $payment_method->get('currency')->getValue() ? $payment_method->get('currency')->getValue()[0]['target_id'] : '';
      }
      $field = $form['payment_details'];
    }

    if ($payment_method && $currency == $payment_method->get('currency')->getValue()[0]['target_id']) {
      $form_state->setError($field, t('You already have a virtual wallet for this currency.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method */
    $payment_method = $this->entity;

    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsStoredPaymentMethodsInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $this->plugin;

    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $this->routeMatch->getParameter('commerce_order');

    /** @var \Drupal\profile\Entity\ProfileInterface $billing_profile */
    $billing_profile = $payment_method->getBillingProfile();
    if (!$billing_profile) {
      /** @var \Drupal\profile\Entity\ProfileInterface $billing_profile */
      $billing_profile = Profile::create([
        'type' => 'funds_payment',
        'uid' => $payment_method->getOwnerId(),
      ]);
    }
    $payment_method->setBillingProfile($billing_profile);

    // The payment method form is customer facing. For security reasons
    // the returned errors need to be more generic.
    if (!$order) {
      $currency = $form_state->getValue('currency');
    }
    else {
      $currency = $order->getTotalPrice()->getCurrencyCode();
    }
    try {
      $payment_gateway_plugin->createPaymentMethod($payment_method, [
        'balance_id' => $payment_method->getOwnerId(),
        'currency' => $currency,
      ]);
    }
    catch (DeclineException $e) {
      $this->logger->warning($e->getMessage());
      throw new DeclineException(t('We encountered an error processing your payment method. Please verify your details and try again.'));
    }
    catch (PaymentGatewayException $e) {
      $this->logger->error($e->getMessage());
      throw new PaymentGatewayException(t('We encountered an unexpected error processing your payment method. Please try again later.'));
    }
  }

  /**
   * Load the currency balance PaymentMethod.
   *
   * @param int $uid
   *   The profile user uid.
   * @param string $currency
   *   The profile currency code.
   *
   * @return Drupal\commerce_payment\Entity\PaymentMethod
   *   The currency balance of the user.
   */
  public function loadPaymentMethod($uid, $currency) {
    $balance = $this->entityTypeManager->getStorage('commerce_payment_method')->loadByProperties([
      'type' => 'funds_wallet',
      'uid' => $uid,
      'currency' => $currency,
    ]);

    return reset($balance);
  }

}
