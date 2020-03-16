<?php

namespace Drupal\alter_entity_autocomplete\AlterEntityAutocomplete;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcher;

/**
 *
 */
class AlterEntityAutocompleteMatcher extends EntityAutocompleteMatcher {

  /**
   * Gets matched labels based on a given search string.
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {

  $matches = [];
     $options = $selection_settings + [
      'target_type' => $target_type,
      'handler' => $selection_handler,
    ];
    $handler = $this->selectionManager->getInstance($options);

    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $match_limit = isset($selection_settings['match_limit']) ? (int) $selection_settings['match_limit'] : 10;
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, $match_limit);


      $target_entity_type = \Drupal::config('alter_entity_autocomplete.settings')->get('alter_autocomplete_entity');
      $target_entity_field = \Drupal::config('alter_entity_autocomplete.settings')->get('alter_autocomplete_field_name');

      if (!empty($target_entity_type) && !empty($target_entity_field)) {
        $query = \Drupal::entityQuery('user');
        $query->condition('status', TRUE);
        $query->condition($target_entity_field, '%' . db_like($string) . '%', 'like');
        $query->range(0, $match_limit);
        $result = $query->execute();

        if (empty($result)) {
          return [];
        }

        $entity_labels = [];
        $entities = \Drupal::entityTypeManager()->getStorage($target_type)->loadMultiple($result);
        foreach ($entities as $entity_id => $entity) {
          $bundle = $entity->bundle();
          if (!empty($target_entity_type) && !empty($target_entity_field)) {
            $entity_labels[$bundle][$entity_id] = !empty($entity->$target_entity_field->value) ? $entity->$target_entity_field->value : $entity->label();
          }
        }
      }
      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {
          $key = "$label ($entity_id)";
          // Strip things like starting/trailing white spaces, line breaks and
          // tags.
          $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(strip_tags($key))));
          // Names containing commas or quotes must be wrapped in quotes.
          $key = Tags::encode($key);

          $matches[] = ['value' => $key, 'label' => $label];

        }
      }
    }
    return $matches;
  }

}
