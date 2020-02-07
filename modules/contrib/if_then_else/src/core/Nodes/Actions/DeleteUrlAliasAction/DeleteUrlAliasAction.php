<?php

namespace Drupal\if_then_else\core\Nodes\Actions\DeleteUrlAliasAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * Delete URL alias action class.
 */
class DeleteUrlAliasAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'delete_url_alias_action';
  }

  /**
   * The module manager.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $pathAliasStorage;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;
  /**
   * The AliasManager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The entity type manager.
   * @param \Drupal\Core\Path\AliasStorageInterface $path_alias_storage
   *   The Alias storage.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The alias manager.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler,
                              AliasStorageInterface $path_alias_storage,
                              LoggerChannelFactoryInterface $loggerFactory,
                              AliasManagerInterface $aliasManager) {
    $this->moduleHandler = $moduleHandler;
    $this->pathAliasStorage = $path_alias_storage;
    $this->loggerFactory = $loggerFactory->get('if_then_else');
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Delete URL Alias'),
      'description' => $this->t('Delete URL Alias'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\DeleteUrlAliasAction\\DeleteUrlAliasAction',
      'classArg' => ['module_handler', 'path.alias_storage',
        'logger.factory', 'path.alias_manager',
      ],
      'dependencies' => ['path'],
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    // To check path module is enable or not.
    if (!$this->moduleHandler->moduleExists('path')) {
      $this->loggerFactory->notice($this->t("Path module is not enabled. Rule @node_name won't execute.", ['@node_name' => $this->data->name]));
      $this->setSuccess(FALSE);
      return;
    }

    /** @var \Drupal\Core\Entity\EntityBase $entity */
    $entity = $this->inputs['entity'];
    if (!$entity instanceof EntityInterface) {
      $this->setSuccess(FALSE);
      return;
    }
    $alias_exists = $this->aliasManager->getAliasByPath('/' . $entity->toUrl()->getInternalPath());
    if (empty($alias_exists)) {
      $this->setSuccess(FALSE);
      return;
    }
    // Delete all aliases associated with this entity in the current language.
    $conditions = [
      'source' => '/' . $entity->toUrl()->getInternalPath(),
      'langcode' => $entity->language()->getId(),
    ];
    $this->pathAliasStorage->delete($conditions);
  }

}
