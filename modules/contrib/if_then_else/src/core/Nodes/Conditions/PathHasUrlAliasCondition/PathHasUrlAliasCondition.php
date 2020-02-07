<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\PathHasUrlAliasCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * Path Has URL Alias condition class.
 */
class PathHasUrlAliasCondition extends Condition {
  use StringTranslationTrait;

  /**
   * The AliasManager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The alias manager.
   */
  public function __construct(AliasManagerInterface $aliasManager) {
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'path_has_url_alias_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Path Has URL Alias'),
      'description' => $this->t('Path Has URL Alias'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\PathHasUrlAliasCondition\\PathHasUrlAliasCondition',
      'classArg' => ['path.alias_manager'],
      'inputs' => [
        'path' => [
          'label' => $this->t('Path'),
          'description' => $this->t('The path to check.'),
          'sockets' => ['string.url', 'string'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the path has an alias in the given language.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $path = trim($this->inputs['path']);
    $alias = $this->aliasManager->getAliasByPath($path);

    $output = FALSE;
    if ($alias != $path) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
