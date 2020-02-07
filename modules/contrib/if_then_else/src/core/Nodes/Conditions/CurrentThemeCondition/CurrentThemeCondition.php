<?php

namespace Drupal\if_then_else\core\Nodes\Conditions\CurrentThemeCondition;

use Drupal\if_then_else\core\Nodes\Conditions\Condition;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Current Theme condition class.
 */
class CurrentThemeCondition extends Condition {
  use StringTranslationTrait;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The theme Manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme Manager.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ThemeManagerInterface $theme_manager) {
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'current_theme_condition';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $theme_lists = $this->themeHandler->listInfo();
    $admin_theme_options = [];
    foreach ($theme_lists as $theme) {
      if (!empty($theme->status)) {
        $admin_theme_options[] = ['code' => $theme->getName(), 'name' => $theme->info['name']];
      }
    }
    $event->nodes[static::getName()] = [
      'label' => $this->t('Current Theme'),
      'description' => $this->t('Current Theme'),
      'type' => 'condition',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Conditions\\CurrentThemeCondition\\CurrentThemeCondition',
      'classArg' => ['theme_handler', 'theme.manager'],
      'library' => 'if_then_else/CurrentThemeCondition',
      'control_class_name' => 'CurrentThemeConditionControl',
      'compare_options' => $admin_theme_options,
      'outputs' => [
        'success' => [
          'label' => $this->t('Success'),
          'description' => $this->t('TRUE if the current theme is of the provided theme.'),
          'socket' => 'bool',
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!isset($data->selected_theme[0]->code) || empty($data->selected_theme[0]->code)) {
      $event->errors[] = $this->t('Select a theme for "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $selected_theme = $this->data->selected_theme[0]->code;
    $current_theme = $this->themeManager->getActiveTheme()->getName();

    $output = FALSE;

    if ($selected_theme == $current_theme) {
      $output = TRUE;
    }

    $this->outputs['success'] = $output;

  }

}
