<?php

namespace Drupal\if_then_else\core\Nodes\Actions\SaveEntityAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Delete entity action node class.
 */
class SaveEntityAction extends Action {
  use StringTranslationTrait;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
    $this->loggerFactory = $loggerFactory->get('if_then_else');
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'save_entity_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Save Entity'),
      'description' => $this->t('Save Entity'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\SaveEntityAction\\SaveEntityAction',
      'classArg' => ['logger.factory'],
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity Object'),
          'socket' => 'object.entity',
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}.
   */
  public function process() {
    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];

    if (!$entity instanceof EntityInterface) {
      $this->loggerFactory->notice($this->t("Rule @node_name did not run as the instance of the entity could not be found", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }
    $entity->save();

    // Adding the entity object in the output.
    $this->outputs['entity'] = $entity;

  }

}
