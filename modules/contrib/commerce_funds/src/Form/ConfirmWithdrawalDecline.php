<?php

namespace Drupal\commerce_funds\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\commerce_funds\Entity\Transaction;

/**
 * Defines a confirmation form to decline a withdrawal request.
 */
class ConfirmWithdrawalDecline extends ConfirmFormBase {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The token utility.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   */
  public function __construct(MailManagerInterface $mail_manager, Token $token, MessengerInterface $messenger) {
    $this->mailManager = $mail_manager;
    $this->token = $token;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('token'),
      $container->get('messenger')
    );
  }

  /**
   * ID of the withdrawal request.
   *
   * @var int
   */
  protected $requestId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "confirm_withdrawal_decline";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $request_id = NULL) {
    $this->requestId = $request_id;

    $form['reason'] = [
      '#type' => 'text_format',
      '#title' => t('Reason for decline'),
      '#description' => $this->t('The message will be addressed to the requester by email.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('view.commerce_funds_transactions.withdrawal_requests');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to decline request: %id?', ['%id' => $this->requestId]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load the request.
    $transaction = Transaction::load($this->requestId);

    // Update request.
    $transaction->setStatus('Declined');
    $transaction->setNotes($form_state->getValue('reason'));
    $transaction->save();

    // Send an email to the requester.
    $requester = $transaction->getIssuer();
    $langcode = $this->config('system.site')->get('langcode');
    $config = $this->config('commerce_funds.settings');

    if ($config->get('mail_withdrawal_declined')['activated']) {
      $balance = \Drupal::service('commerce_funds.transaction_manager')->loadAccountBalance($requester);
      $params = [
        'id' => 'withdrawal_declined',
        'subject' => $this->token->replace($config->get('mail_withdrawal_declined')['subject'], ['commerce_funds_transaction' => $transaction]),
        'body' => $this->token->replace($config->get('mail_withdrawal_declined')['body']['value'], [
          'commerce_funds_transaction' => $transaction,
          'commerce_funds_balance' => $balance,
          'commerce_funds_balance_uid' => $requester->id(),
        ]),
      ];
      $this->mailManager->mail('commerce_funds', 'commerce_funds_transaction', $requester->getEmail(), $langcode, $params, NULL, TRUE);

      $message = $this->t('Request declined. An email with the reason has been sent to @user');
    }

    // Confirmation message.
    $this->messenger->addMessage(isset($message) ? $message : $this->t('Request declined.'), 'status');

    // Set redirection.
    $form_state->setRedirect('view.commerce_funds_transactions.withdrawal_requests');
  }

}
