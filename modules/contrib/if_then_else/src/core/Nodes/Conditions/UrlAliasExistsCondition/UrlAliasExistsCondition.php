<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\UrlAliasExistsCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * URL Alias Exists condition class.
 */
class UrlAliasExistsCondition extends Condition {
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
    return 'url_alias_exists_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('URL Alias Exists'),
      'description' => $this->t('URL Alias Exists'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\UrlAliasExistsCondition\\UrlAliasExistsCondition',
      'classArg' => ['path.alias_manager'],
      'inputs' => [
        'alias' => [
          'label' => $this->t('Alias'),
          'description' => $this->t('The alias to see if exists.'),
          'sockets' => ['string.url', 'string'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the system path does not match the given alias (ie: the alias exists).'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function process() {

    $alias = trim($this->inputs['alias']);
    $path = $this->aliasManager->getPathByAlias($alias);

    $output = FALSE;
    if ($path != $alias) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
