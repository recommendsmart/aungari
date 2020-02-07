<?php

namespace Drupal\if_then_else\Controller;

use Drupal\Component\Utility\Html;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * IftheneelseRule module controller class to define url callbacks.
 */
class ReteIntegration {

  /**
   * Ajax Url route callback to fetch fields of forms.
   */
  public function fetchFieldInfo($entity_name, $bundle_name, $field_name) {
    // Calling ifthenelse utilities service.
    $ifthenelseUtilities = \Drupal::service('ifthenelse.utilities');

    if (isset($entity_name) && isset($bundle_name) && isset($field_name)) {
      $entity_type_id = Html::escape($entity_name);
      $entity_bundle = Html::escape($bundle_name);
      $field_name = Html::escape($field_name);

      // Get list of fields by entity and bundle id.
      $listFields = $ifthenelseUtilities->getFieldInfoByEntityBundleId($entity_type_id, $entity_bundle, $field_name);
    }

    return new JsonResponse([
      'data' => $listFields,
    ]);
  }

}
