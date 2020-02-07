<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SendEmailAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Class defined to execute send email action node.
 */
class SendEmailAction extends Action {
  use StringTranslationTrait;

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * Current account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The Mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account.
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The Mail Manager.
   */
  public function __construct(EmailValidatorInterface $email_validator, AccountProxyInterface $account, MailManagerInterface $mailManager) {
    $this->emailValidator = $email_validator;
    $this->account = $account;
    $this->mailManager = $mailManager;
  }

  /**
   * Return node name.
   */
  public static function getName() {
    return 'send_email_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {

    $event->nodes[static::getName()] = [
      'label' => $this->t('Send Email'),
      'description' => $this->t('Send Email'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SendEmailAction\\SendEmailAction',
      'classArg' => ['email.validator', 'current_user', 'plugin.manager.mail'],
      'inputs' => [
        'to' => [
          'label' => $this->t('To'),
          'description' => $this->t('To email address'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'from' => [
          'label' => $this->t('From'),
          'description' => $this->t('From email address'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'subject' => [
          'label' => $this->t('Subject'),
          'description' => $this->t('Email subject'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
        'body' => [
          'label' => $this->t('Body'),
          'description' => $this->t('Email body'),
          'sockets' => ['string'],
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
      if ($node->data->type == 'value' && $node->data->name == 'text_value') {
        // To check empty input.
        if (!property_exists($node->data, 'value') || empty($node->data->value)) {
          $event->errors[] = $this->t('Enter the value for  "@node_name".', ['@node_name' => $node->name]);
        }
        else {
          // Validate email.
          $email = trim($node->data->value);
          foreach ($node->outputs->text->connections as $connection) {
            if ($connection->input == 'to' &&  !$this->emailValidator->isValid($email)) {
              $event->errors[] = $this->t('Enter valid email for input "To" in "@node_name".', ['@node_name' => $node->name]);

            }
            if ($connection->input == 'from' && !$this->emailValidator->isValid($email)) {
              $event->errors[] = $this->t('Enter valid email for input "From" in "@node_name".', ['@node_name' => $node->name]);
            }
          }
        }
      }
    }
  }

  /**
   * Process send email action node.
   */
  public function process() {
    $to = $this->inputs['to'];
    $from = $this->inputs['from'];
    $params['subject'] = $this->inputs['subject'];
    $params['body'] = $this->inputs['body'];
    $langcode = $this->account->getPreferredLangcode();
    $this->mailManager->mail('if_then_else', 'send_email_if_then_else', $to, $langcode, $params, $from, TRUE);
  }

}
