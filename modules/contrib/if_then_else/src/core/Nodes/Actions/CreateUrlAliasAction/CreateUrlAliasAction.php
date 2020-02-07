<?php

namespace Drupal\if_then_else\core\Nodes\Actions\CreateUrlAliasAction;

use Drupal\Core\Entity\EntityInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Create URL alias action class.
 */
class CreateUrlAliasAction extends Action {
  use StringTranslationTrait;

  /**
   * The module manager.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The alias storage.
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
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The entity type manager.
   * @param \Drupal\Core\Path\AliasStorageInterface $path_alias_storage
   *   The The alias storage.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler,
                              AliasStorageInterface $path_alias_storage,
                              LoggerChannelFactoryInterface $loggerFactory) {
    $this->moduleHandler = $moduleHandler;
    $this->pathAliasStorage = $path_alias_storage;
    $this->loggerFactory = $loggerFactory->get('if_then_else');
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'create_url_alias_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Create URL Alias'),
      'description' => $this->t('Create URL Alias'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\CreateUrlAliasAction\\CreateUrlAliasAction',
      'classArg' => ['module_handler', 'path.alias_storage', 'logger.factory'],
      'dependencies' => ['path'],
      'inputs' => [
        'alias' => [
          'label' => $this->t('Alias'),
          'description' => $this->t('Set alias to entity.'),
          'sockets' => ['string'],
          'required' => TRUE,
        ],
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
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'value' && $node->data->name == 'text_value') {
        // To check empty input.
        if (!property_exists($node->data, 'value') || empty($node->data->value)) {
          $event->errors[] = $this->t('Enter the alias in "@node_name".', ['@node_name' => $node->name]);
        }
        else {
          // To check valid url.
          if (!UrlHelper::isValid($node->data->value)) {
            $event->errors[] = $this->t('@alias is not a valid URL in "@node_name".', ['@alias' => $node->data->value, '@node_name' => $node->name]);
          }
        }
      }
    }
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
    $alias = $this->inputs['alias'];
    // Checking slash if not exist then adding slash to alias.
    $alias = rtrim(trim(trim($alias), ''), "\\/");
    if ($alias[0] !== '/') {
      $alias = '/' . $alias;
    }
    if (!$entity instanceof EntityInterface || empty($alias)) {
      $this->setSuccess(FALSE);
      return;
    }
    $alias_langcode = $entity->language()->getId();
    $alias_exists = $this->pathAliasStorage->aliasExists($alias, $alias_langcode);
    if (!$alias_exists) {
      $this->pathAliasStorage->save('/' . $entity->toUrl()->getInternalPath(), $alias, $alias_langcode);
    }
  }

}
