<?php

namespace Drupal\inline_entity_revision_form\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\inline_entity_revision_form\ElementSubmit;
use Drupal\inline_entity_revision_form\TranslationHelper;

/**
 * Provides an inline entity revision form element.
 *
 * Usage example:
 * @code
 * $form['article'] = [
 *   '#type' => 'inline_entity_revision_form',
 *   '#entity_type' => 'node',
 *   '#bundle' => 'article',
 *   // If the #default_value is NULL, a new entity will be created.
 *   '#default_value' => $loaded_article,
 * ];
 * @endcode
 * To access the entity in validation or submission callbacks, use
 * $form['article']['#entity']. Due to Drupal core limitations the entity
 * can't be accessed via $form_state->getValue('article').
 *
 * @RenderElement("inline_entity_revision_form")
 */
class InlineEntityRevisionForm extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#ief_id' => '',
      '#entity_type' => NULL,
      '#bundle' => NULL,
      '#langcode' => NULL,
      // Instance of \Drupal\Core\Entity\EntityInterface. If NULL, a new
      // entity will be created.
      '#default_value' => NULL,
      // The form mode used to display the entity form.
      '#form_mode' => 'default',
      // Will save entity on submit if set to TRUE.
      '#save_entity' => TRUE,
      // 'add', 'edit' or 'duplicate'.
      '#op' => NULL,
      '#process' => [
        // Core's #process for groups, don't remove it.
        [$class, 'processGroup'],

        // InlineEntityForm's #process must run after the above ::processGroup
        // in case any new elements (like groups) were added in alter hooks.
        [$class, 'processEntityForm'],
      ],
      '#element_validate' => [
        [$class, 'validateEntityForm'],
      ],
      '#ief_element_submit' => [
        [$class, 'submitEntityForm'],
      ],
      '#theme_wrappers' => ['container'],

      '#pre_render' => [
        // Core's #pre_render for groups, don't remove it.
        [$class, 'preRenderGroup'],
      ],
    ];
  }

  /**
   * Builds the entity form using the inline form handler.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the #entity_type or #bundle properties are empty, or when
   *   the #default_value property is not an entity.
   *
   * @return array
   *   The built entity form.
   */
  public static function processEntityForm($entity_form, FormStateInterface $form_state, &$complete_form) {
    if (empty($entity_form['#entity_type'])) {
      throw new \InvalidArgumentException('The inline_entity_revision_form element requires the #entity_type property.');
    }
    if (isset($entity_form['#default_value']) && !($entity_form['#default_value'] instanceof EntityInterface)) {
      throw new \InvalidArgumentException('The inline_entity_revision_form #default_value property must be an entity object.');
    }

    if (empty($entity_form['#ief_id'])) {
      $entity_form['#ief_id'] = \Drupal::service('uuid')->generate();
    }

    if (isset($entity_form['#default_value'])) {
      // Transfer the #default_value to #entity, as expected by inline forms.

//       dsm( $form_state->getUserInput()['field_group'][0]['inline_entity_revision_form']['field_section'][0]['inline_entity_revision_form']['field_link_type_new'][0]['inline_entity_revision_form'] );
//       dsm( $entity_form['#default_value'] );

      $entity_form['#entity'] = $entity_form['#default_value'];
      $entity_form['#entity']->setNewRevision();

//       if( !empty( $form_state->getUserInput() ) ) {

//         $arr_user_input = $form_state->getUserInput();

//         $arr_user_input_depth_1 = [];

//         $obj_entity_node = \Drupal::routeMatch()->getParameter('node');

//         foreach( $arr_user_input as $key_1 => $arr_user_input_key_value_1 ) {
//           if( !empty( $arr_user_input_key_value_1 ) && is_array( $arr_user_input_key_value_1 ) ) {
//             foreach( $arr_user_input_key_value_1 as $arr_user_input_value_1 ) {
//               if( isset( $arr_user_input_value_1['inline_entity_revision_form'] ) ) {
//                 if( $obj_entity_node->hasField($key_1) ) {
//                   $arr_user_input_depth_1 = $arr_user_input_value_1['inline_entity_revision_form'];
//                   $arr_obj_entity_depth_1 = $obj_entity_node->get($key_1)->referencedEntities();
//                   foreach( $arr_user_input_depth_1 as $key_2 => $arr_user_input_key_value_2 ) {
//                     if( !empty( $arr_user_input_key_value_2 ) && is_array( $arr_user_input_key_value_2 ) ) {
//                       foreach( $arr_user_input_key_value_2 as $arr_user_input_value_2 ) {
//                         if( isset( $arr_user_input_value_2['inline_entity_revision_form'] ) ) {
//                           foreach( $arr_obj_entity_depth_1 as $obj_entity_depth_1 ) {
//                               if( $obj_entity_depth_1->hasField($key_2) ) {
//                                 $arr_depth_2 = [];
//                                 $arr_user_input_depth_2 = $arr_user_input_value_2['inline_entity_revision_form'];
//                                 $arr_obj_entity_depth_2 = $obj_entity_depth_1->get($key_2)->referencedEntities();
//                                 foreach( $arr_obj_entity_depth_2 as $obj_entity_depth_2 ) {
//                                   $str_entity_type_2 = $obj_entity_depth_2->getEntityType()->id();
//                                   foreach( $arr_user_input_depth_2 as $key_3 => $arr_user_input_key_value_3 ) {
//                                     if( !empty( $arr_user_input_key_value_3 ) && is_array( $arr_user_input_key_value_3 ) ) {
//                                       foreach( $arr_user_input_key_value_3 as $arr_user_input_value_3 ) {
//                                         if( isset( $arr_user_input_value_3['inline_entity_revision_form'] ) ) {
//                                           if( $obj_entity_depth_2->hasField($key_3) ) {
//                                             $arr_depth_3 = [];
//                                             $arr_user_input_depth_3 = $arr_user_input_value_3['inline_entity_revision_form'];
//                                             $arr_obj_entity_depth_3 = $obj_entity_depth_2->get($key_3)->referencedEntities();
//                                             foreach( $arr_obj_entity_depth_3 as $obj_entity_depth_3 ) {
//                                               $str_entity_type_3 = $obj_entity_depth_3->getEntityType()->id();
//                                               foreach( $arr_user_input_depth_3 as $key_4 => $arr_user_input_value_4 ) {
//                                                 setDataAsPerFieldType($key_4, $obj_entity_depth_3, $arr_user_input_depth_3);
//                                               }
//                                               $obj_entity_depth_3->save();
//                                               $lattest_revision_id = _inline_get_latest_revision( \Drupal::entityTypeManager()->getStorage($str_entity_type_3)->load($obj_entity_depth_2->id()), $str_entity_type_3);
//                                               $arr_depth_3[$obj_entity_depth_3->id()] = ['target_id' => $obj_entity_depth_3->id(), 'target_revision_id' => $lattest_revision_id ];
//                                             }
//                                             $obj_entity_depth_2->set($key_3, $arr_depth_3);
//                                           }
//                                         }
//                                       }
//                                     }
//                                     setDataAsPerFieldType($key_3, $obj_entity_depth_2, $arr_user_input_depth_2);
//                                   }
//                                   $obj_entity_depth_2->save();
//                                   $lattest_revision_id = _inline_get_latest_revision( \Drupal::entityTypeManager()->getStorage($str_entity_type_2)->load($obj_entity_depth_2->id()), $str_entity_type_2);
//                                   $arr_depth_2[$obj_entity_depth_2->id()] = ['target_id' => $obj_entity_depth_2->id(), 'target_revision_id' => $lattest_revision_id ];
//                                 }
//                                 $obj_entity_depth_1->set($key_2, $arr_depth_2);
//                               }
//                             }
//                         }
//                       }
//                     }
//                   }
//                 }
//               }
//             }
//           }
//         }

// //               dsm(3);
//         // This is an add operation, create a new entity.
// //         $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_form['#entity_type']);
// //         $storage = \Drupal::entityTypeManager()->getStorage($entity_form['#entity_type']);

// // //         dsm( $entity_type->getKey('bundle') );
// // //         dsm( $entity_form['#bundle'] );
// // //         dsm( $entity_form['#entity_type'] );

// //         $values = [];
// //         if ($langcode_key = $entity_type->getKey('langcode')) {
// //           if (!empty($entity_form['#langcode'])) {
// //             $values[$langcode_key] = $entity_form['#langcode'];
// //           }
// //         }
// //         if ($bundle_key = $entity_type->getKey('bundle')) {
// //           $values[$bundle_key] = $entity_form['#bundle'];
// //         }
// //         $entity_form['#entity'] = $storage->create($values);
// //          dsm( \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_form['#default_value']->id()));
//       }
    }
    else {

      // This is an add operation, create a new entity.
      $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_form['#entity_type']);
      $storage = \Drupal::entityTypeManager()->getStorage($entity_form['#entity_type']);

      $values = [];
      if ($langcode_key = $entity_type->getKey('langcode')) {
        if (!empty($entity_form['#langcode'])) {
          $values[$langcode_key] = $entity_form['#langcode'];
        }
      }

      if ($bundle_key = $entity_type->getKey('bundle')) {
        $values[$bundle_key] = $entity_form['#bundle'];
      }

      $entity_form['#entity'] = $storage->create($values);

    }
    if (!isset($entity_form['#op'])) {
      // When duplicating entities, the entity is new, but already has a UUID.
      if ($entity_form['#entity']->isNew() && $entity_form['#entity']->uuid()) {
        $entity_form['#op'] = 'duplicate';
      }
      else {
        $entity_form['#op'] = $entity_form['#entity']->isNew() ? 'add' : 'edit';
      }
    }
    // Prepare the entity form and the entity itself for translating.
    $entity_form['#entity'] = TranslationHelper::prepareEntity($entity_form['#entity'], $form_state);
    $entity_form['#translating'] = TranslationHelper::isTranslating($form_state) && $entity_form['#entity']->isTranslatable();

    $inline_form_handler = static::getInlineRevisionFormHandler($entity_form['#entity_type']);

    $entity_form = $inline_form_handler->entityForm($entity_form, $form_state);
//     return $entity_form;
    // The form element can't rely on inline_entity_revision_form_form_alter() calling
    // ElementSubmit::attach() since form alters run before #process callbacks.
//     return $entity_form;
    ElementSubmit::attach($complete_form, $form_state);

//     print '<pre>Hi';
//     print_r( array_keys( $entity_form ) );
//     die;

    return $entity_form;

  }

  /**
   * Validates the entity form using the inline form handler.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateEntityForm(&$entity_form, FormStateInterface $form_state) {
    $inline_form_handler = static::getInlineRevisionFormHandler($entity_form['#entity_type']);
    $inline_form_handler->entityFormValidate($entity_form, $form_state);
  }

  /**
   * Handles the submission of the entity form using the inline form handler.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function submitEntityForm(&$entity_form, FormStateInterface $form_state) {
    $inline_form_handler = static::getInlineRevisionFormHandler($entity_form['#entity_type']);
    $inline_form_handler->entityFormSubmit($entity_form, $form_state);
    if ($entity_form['#save_entity']) {
      $inline_form_handler->save($entity_form['#entity']);
    }
  }

  /**
   * Gets the inline form handler for the given entity type.
   *
   * @param string $entity_type
   *   The entity type id.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the entity type has no inline form handler defined.
   *
   * @return \Drupal\inline_entity_revision_form\InlineRevisionFormInterface
   *   The inline revision form handler.
   */
  public static function getInlineRevisionFormHandler($entity_type) {
    $inline_form_handler = \Drupal::entityTypeManager()->getHandler($entity_type, 'inline_revision_form');
    if (empty($inline_form_handler)) {
      throw new \InvalidArgumentException(sprintf('The %s entity type has no inline form handler.', $entity_type));
    }
    return $inline_form_handler;
  }

  /**
   * Gets the inline form handler for the given entity type.
   *
   * @param string $entity_type
   *   The entity type id.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the entity type has no inline form handler defined.
   *
   * @return \Drupal\inline_entity_form\InlineFormInterface
   *   The inline form handler.
   */
  public static function getInlineFormHandler($entity_type) {
    $inline_form_handler = \Drupal::entityTypeManager()->getHandler($entity_type, 'inline_form');
    if (empty($inline_form_handler)) {
      throw new \InvalidArgumentException(sprintf('The %s entity type has no inline form handler.', $entity_type));
    }

    return $inline_form_handler;
  }

}
