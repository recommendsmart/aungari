<?php

namespace Drupal\if_then_else\core\Nodes\Actions\ParseImageFieldValueAction;

use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Parse Image field value node class.
 */
class ParseImageFieldValueAction extends Action {
  use StringTranslationTrait;

  /**
   * Return name of Image field value action node.
   */
  public static function getName() {
    return 'image_field_value_action';
  }

  /**
   * Event subscriber for registering Image field value action node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Parse Image Field'),
      'description' => $this->t('Parse Image Field'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\ParseImageFieldValueAction\\ParseImageFieldValueAction',
      'inputs' => [
        'field_value' => [
          'label' => $this->t('Image field object'),
          'description' => $this->t('Image field object'),
          'sockets' => ['object.field.image'],
          'required' => TRUE,
        ],
      ],
      'outputs' => [
        'alt' => [
          'label' => $this->t('Image Alt'),
          'description' => $this->t('Image Uri'),
          'socket' => 'string',
        ],
        'fid' => [
          'label' => $this->t('Image Fid'),
          'description' => $this->t('Image Fid'),
          'socket' => 'number',
        ],
        'width' => [
          'label' => $this->t('Image Width'),
          'description' => $this->t('Image width'),
          'socket' => 'number',
        ],
        'height' => [
          'label' => $this->t('Image Height'),
          'description' => $this->t('Image Title'),
          'socket' => 'number',
        ],
        'description' => [
          'label' => $this->t('Image Description'),
          'description' => $this->t('Image Uri'),
          'socket' => 'string',
        ],
        'title' => [
          'label' => $this->t('Image Title'),
          'description' => $this->t('Image Uri'),
          'socket' => 'string',
        ],
      ],
    ];
  }

  /**
   * Process function for Image field value action node.
   */
  public function process() {
    $this->outputs['alt'] = $this->inputs['field_value']->alt;
    $this->outputs['fid'] = $this->inputs['field_value']->fids[0];
    $this->outputs['width'] = $this->inputs['field_value']->width;
    $this->outputs['height'] = $this->inputs['field_value']->height;
    $this->outputs['description'] = $this->inputs['field_value']->description;
    $this->outputs['title'] = $this->inputs['field_value']->title;
  }

}
