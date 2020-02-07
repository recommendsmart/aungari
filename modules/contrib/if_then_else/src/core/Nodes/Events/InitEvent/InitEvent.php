<?php

namespace Drupal\if_then_else\core\Nodes\Events\InitEvent;

use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else\Event\EventFilterEvent;
use Drupal\if_then_else\Event\EventConditionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Init event node class.
 */
class InitEvent extends Event {
  use StringTranslationTrait;

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'init_event';
  }

  /**
   * Event subscriber for init event node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Init'),
      'description' => $this->t('Init'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\InitEvent\\InitEvent',
      'library' => 'if_then_else/InitEvent',
      'control_class_name' => 'InitEventControl',
      'outputs' => [
        'url' => [
          'label' => $this->t('Requested URL'),
          'description' => $this->t('Requested URL.'),
          'socket' => 'string.url',
        ],
      ],
    ];
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!property_exists($data, 'form_selection')) {
      $event->errors[] = $this->t('Select the Match Condition in "@node_name".', ['@node_name' => $event->node->name]);
      return;
    }
    if ($data->form_selection != 'all' && (!property_exists($data, 'valueText') || empty($data->valueText))) {
      $event->errors[] = $this->t('Enter path to match with requested path in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions(EventConditionEvent $event) {
    $data = $event->data;
    if ($data->form_selection == 'all') {
      $event->conditions[] = self::getName() . '::all';
    }
    elseif ($data->form_selection == 'other') {
      // Checking slash if not exist then adding slash to alias.
      $path = rtrim(trim(trim($data->valueText), ''), "\\/");
      $path_str_len = strlen($path);
      if (!empty($path[0]) && $path[0] !== '/') {
        $path = '/' . $path;
      }
      if (!empty($path_str_len) && $path[$path_str_len - 1] === '/') {
        $path = substr($path, 0, $path_str_len - 2);
      }
      $event->conditions[] = self::getName() . '_other_' . $path;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function filterEvents(EventFilterEvent $event) {
    $request_uri = $event->args['url'];
    if (empty($request_uri)) {
      $event->query->condition('event', '');
      return;
    }
    $path_str_len = strlen($request_uri);
    if (!empty($path_str_len) && $request_uri[$path_str_len - 1] === '/') {
      $request_uri = substr($request_uri, 0, $path_str_len - 1);
    }
    $paths = explode('/', $request_uri);
    $dynamic_path = [];
    foreach ($paths as $path) {
      if (is_numeric($path)) {
        $dynamic_path[] = '%';
      }
      else {
        $dynamic_path[] = $path;
      }
    }
    $dynamic_path = implode('/', $dynamic_path);

    $or = $event->query->orConditionGroup()
      ->condition('condition', self::getName() . '::all', 'CONTAINS')
      ->condition('condition', self::getName() . '_other_' . $request_uri, 'CONTAINS')
      ->condition('condition', self::getName() . '_other_' . $dynamic_path, 'CONTAINS');

    $event->query->condition($or);
  }

}
