<?php

namespace Drupal\if_then_else\core\Nodes\Actions\BanIpAddressAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Banned IP address action node class.
 */
class BanIpAddressAction extends Action {
  use StringTranslationTrait;
  /**
   * The module manager.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler,
                              LoggerChannelFactoryInterface $loggerFactory) {
    $this->moduleHandler = $moduleHandler;
    $this->loggerFactory = $loggerFactory->get('if_then_else');
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'ban_ip_address_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Ban IP Address'),
      'description' => $this->t('Ban IP Address'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\BanIpAddressAction\\BanIpAddressAction',
      'classArg' => ['module_handler', 'logger.factory'],
      'dependencies' => ['ban'],
      'inputs' => [
        'ip_address' => [
          'label' => $this->t('IP address / List of IP address'),
          'description' => $this->t('Can be a comma-separated string of IP or an array of IP.'),
          'sockets' => ['string', 'array'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {

    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'text_value' && property_exists($node->data, 'value')) {
        $ip_addresses = $node->data->value;
        $banned_ip_addresses = [];
        if (is_string($ip_addresses)) {
          foreach (explode(',', $ip_addresses) as $ip_address) {
            if (!empty($ip_address)) {
              $banned_ip_addresses[] = trim($ip_address);
            }
          }
        }
        elseif (is_array($ip_addresses)) {
          array_merge($banned_ip_addresses, $ip_addresses);
        }
        if (empty($banned_ip_addresses)) {
          $event->errors[] = $this->t('Please enter a valid IP to IfThenElse rule');
        }
        else {
          foreach ($banned_ip_addresses as $ip) {
            $ip = trim($ip);
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
              $event->errors[] = $this->t('IP @ip is not valid IP. Please enter a valid IP to IfThenElse rule', ['@ip' => $ip]);
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    // To check ban module is enable or not.
    if (!$this->moduleHandler->moduleExists('ban')) {
      $this->loggerFactory->notice($this->t("Ban module is not enabled. Rule @node_name won't execute.", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }

    $ip_addresses = $this->inputs['ip_address'];

    $banned_ip_addresses = [];

    if (is_string($ip_addresses)) {
      foreach (explode(',', $ip_addresses) as $ip_address) {
        $banned_ip_addresses[] = trim($ip_address);
      }
    }
    elseif (is_array($ip_addresses)) {
      array_merge($banned_ip_addresses, $ip_addresses);
    }

    if (empty($banned_ip_addresses)) {
      $this->setSuccess(FALSE);
      return;
    }

    $ip_manager = \Drupal::service('ban.ip_manager');
    foreach ($banned_ip_addresses as $ip) {
      $ip = trim($ip);
      if (!$ip_manager->isBanned($ip)) {
        $ip_manager->banIp($ip);
      }
    }
  }

}
