<?php

namespace Drupal\views_pretty_path\FilterHandlers;

class BundleFilterHandler extends AbstractFilterHandler implements ViewsPrettyPathFilterHandlerInterface {

  /**
   * @inheritDoc
   */
  public function getTargetedFilterPluginIds() {
    return [
      'bundle',
    ];
  }

  /**
   * @inheritDoc
   */
  public function transformPathValueForViewsQuery($filter_value_string, $filter_data) {
    $raw_values = explode('+', $filter_value_string);
    $return_values = [];
    foreach ($raw_values as $raw_value) {
      $return_values[$raw_value] = $raw_value;
    }
    return $return_values;
  }

  /**
   * @inheritDoc
   */
  public function transformSubmittedValueForUrl($value) {
    return implode('+', $value);
  }
}
