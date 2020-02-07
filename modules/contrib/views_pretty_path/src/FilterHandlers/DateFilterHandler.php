<?php

namespace Drupal\views_pretty_path\FilterHandlers;

class DateFilterHandler extends AbstractFilterHandler implements ViewsPrettyPathFilterHandlerInterface {

  /**
   * @inheritDoc
   */
  public function getTargetedFilterPluginIds() {
    return [
      'date',
      'datetime',
    ];
  }

  /**
   * @inheritDoc
   */
  public function transformPathValueForViewsQuery($filter_value_string, $filter_data) {
    $return_values = [];
    if (!empty($filter_value_string)) {
      $raw_values = explode('+', $filter_value_string);
      $return_values['min'] = str_replace('-', '/', strtolower($raw_values[0]));
      $return_values['max'] = str_replace('-', '/', strtolower($raw_values[1]));
    }
    return $return_values;
  }

  /**
   * @inheritDoc
   */
  public function transformSubmittedValueForUrl($value) {
    $min = str_replace('/', '-', strtolower($value['min']));
    $max = str_replace('/', '-', strtolower($value['max']));
    return empty($min . $max) ? '' : $min . '+' . $max;
  }
}
