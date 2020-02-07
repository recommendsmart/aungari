<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SetCookieAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Class SetCookieAction.
 *
 * @package Drupal\if_then_else\core\Nodes\Actions\SetCookieAction
 */
class SetCookieAction extends Action {
  use StringTranslationTrait;

  /**
   * The time manager.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time manager.
   */
  public function __construct(TimeInterface $time) {
    $this->time = $time;
  }

  /**
   * Return node name.
   */
  public static function getName() {
    return 'set_cookie_action';
  }

  /**
   * Event subscriber for register set cookie node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Set Cookie'),
      'description' => $this->t('Set Cookie'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SetCookieAction\\SetCookieAction',
      'classArg' => ['datetime.time'],
      'inputs' => [
        'name' => [
          'label' => $this->t('Name'),
          'description' => $this->t('Cookie name.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'value' => [
          'label' => $this->t('Value'),
          'description' => $this->t('Cookie value.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'time_to_expire' => [
          'label' => $this->t('Expires'),
          'description' => $this->t('Number of seconds after which the cookie will expire. Defaults to 31536000 seconds, i.e. 1 year.'),
          'sockets' => ['number'],
        ],
      ],
    ];
  }

  /**
   * Process set cookie action node.
   */
  public function process() {
    $time_to_expire = array_key_exists('time_to_expire', $this->inputs) ? $this->inputs['time_to_expire'] : 31536000;
    setrawcookie($this->inputs['name'], rawurlencode($this->inputs['value']),
      $this->time->getRequestTime() + $time_to_expire, '/');
  }

}
