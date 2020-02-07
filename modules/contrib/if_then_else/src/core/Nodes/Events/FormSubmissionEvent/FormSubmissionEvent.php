<?php

namespace Drupal\if_then_else\core\Nodes\Events\FormSubmissionEvent;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\field_ui\Form\FieldConfigEditForm;
use Drupal\if_then_else\core\Nodes\Events\Event;
use Drupal\if_then_else\Event\EventConditionEvent;
use Drupal\if_then_else\Event\EventFilterEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;

/**
 * Form Submission event node class.
 */
class FormSubmissionEvent extends Event {
  use StringTranslationTrait;

  /**
   * The ifthenelse utilities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utilities.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
  }

  /**
   * Return name of node.
   */
  public static function getName() {
    return 'form_submission_event';
  }

  /**
   * Event subscriber for form submission node.
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    // Calling custom service for if then else utilities. To
    // fetch values of entities and bundles.
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();

    $event->nodes[static::getName()] = [
      'label' => $this->t('Form Submission'),
      'description' => $this->t('Form Submission'),
      'type' => 'event',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Events\\FormSubmissionEvent\\FormSubmissionEvent',
      'library' => 'if_then_else/FormSubmissionEvent',
      'control_class_name' => 'FormSubmissionEventControl',
      'entity_info' => $form_entity_info,
      'classArg' => ['ifthenelse.utilities'],
      'outputs' => [
        'form' => [
          'label' => $this->t('Form'),
          'description' => $this->t('Form object.'),
          'socket' => 'form',
        ],
        'form_state' => [
          'label' => $this->t('Form State'),
          'description' => $this->t('Form state object.'),
          'socket' => 'form_state',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;

    if (!property_exists($data, 'form_selection')) {
      $event->errors[] = $this->t('Select the Match Condition in "@node_name".', ['@node_name' => $event->node->name]);
      return;
    }

    if ($data->form_selection == 'list' && (empty($data->selected_entity) || empty($data->selected_bundle))) {
      // Make sure that both selected_entity and selected_bundle are set.
      $event->errors[] = $this->t('Select both entity and bundle in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->form_selection == 'other') {
      if (empty($data->otherFormClass)) {
        $event->errors[] = $this->t('Enter class name of the form in "@node_name".', ['@node_name' => $event->node->name]);
      }
      elseif (!class_exists($data->otherFormClass)) {
        $event->errors[] = $this->t('Class "@class_name" does not exist. Provide a valid form class name in "@node_name".', ['@class_name' => $data->otherFormClass, '@node_name' => $event->node->name]);
      }
      elseif (!is_subclass_of($data->otherFormClass, '\Drupal\Core\Form\FormBase', TRUE)) {
        $event->errors[] = $this->t('Class "@class_name" is not a valid form. Provide a valid form class name in "@node_name".', ['@class_name' => $data->otherFormClass, '@node_name' => $event->node->name]);
      }
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
    elseif ($data->form_selection == 'list') {
      $event->conditions[] = self::getName() . '::entity_form::' . $data->selected_entity->value . '::' . $data->selected_bundle->value;
    }
    elseif ($data->form_selection == 'other') {
      $event->conditions[] = self::getName() . '::other::' . $data->otherFormClass;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function filterEvents(EventFilterEvent $event) {
    /** @var \Drupal\Core\Form\FormState $form_state */
    $form_state = $event->args['form_state'];

    $entity = NULL;
    $bundle = FALSE;
    if ($form_object = $form_state->getFormObject()) {
      if ($form_object instanceof ContentEntityForm || $form_object instanceof FieldConfigEditForm) {
        $entity = $form_object->getEntity();
        $bundle = $entity->bundle();
      }
    }

    $or = $event->query->orConditionGroup()
      ->condition('condition', self::getName() . '::all', 'CONTAINS')
      ->condition('condition', self::getName() . '::other::' . get_class($form_object), 'CONTAINS')
      ->condition('condition', self::getName() . '::other::\\' . get_class($form_object), 'CONTAINS');

    if ($entity && $bundle) {
      $or->condition('condition', self::getName() . '::entity_form::' . $entity->getEntityTypeId() . '::' . $bundle, 'CONTAINS');
    }

    $event->query->condition($or);
  }

}
