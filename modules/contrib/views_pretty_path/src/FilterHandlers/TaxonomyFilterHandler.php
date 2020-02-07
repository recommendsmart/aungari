<?php

namespace Drupal\views_pretty_path\FilterHandlers;

class TaxonomyFilterHandler extends AbstractFilterHandler implements ViewsPrettyPathFilterHandlerInterface {

  /**
   * @inheritDoc
   */
  public function getTargetedFilterPluginIds() {
    return [
      'taxonomy_index_tid',
    ];
  }

  /**
   * @inheritDoc
   */
  public function transformPathValueForViewsQuery($filter_value_string, $filter_data) {
    $raw_values = explode('+', $filter_value_string);
    $return_array = [];
    if (!empty($raw_values)) {
      $query = $this->database
        ->select('taxonomy_term_field_data', 't')
        ->condition('t.vid', $filter_data['vid'])
        ->fields('t', ['tid']);
      $or_group = $query->orConditionGroup();
      $raw_values = array_map(function($value) {return str_replace('-', ' ', $value);}, $raw_values);
      foreach ($raw_values as $raw_value) {
        $raw_value = str_replace('__', '-', $raw_value);
        $decoded_value = $this->decodeUrlWord($raw_value);
        $or_group->condition('name', $this->database->escapeLike($decoded_value), 'LIKE');
      }
      $query->condition($or_group);
      $return_array = $query->execute()->fetchAllKeyed(0,0);
    }
    return $return_array;
  }

  /**
   * @inheritDoc
   */
  public function transformSubmittedValueForUrl($value) {
    $term_names = $this->database
      ->select('taxonomy_term_field_data', 't')
      ->condition('t.tid', array_keys($value), 'IN')
      ->fields('t', ['name'])
      ->execute()->fetchAllKeyed(0,0);
    // Arrange terms alphabetically.
    asort($term_names);
    $term_string = '';
    $count = 0;
    foreach($term_names as $term) {
      $term = str_replace('-', '__', $term);
      $new_term = $this->encodeMultipleWordsForUrl($term);
      $term_string .= $count > 0 ? '+' . $new_term : $new_term;
      $count++;
    }
    return $term_string;
  }
}
