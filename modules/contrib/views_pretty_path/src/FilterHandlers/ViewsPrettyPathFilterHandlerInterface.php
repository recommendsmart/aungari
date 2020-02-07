<?php

namespace Drupal\views_pretty_path\FilterHandlers;

interface ViewsPrettyPathFilterHandlerInterface {

  /**
   * Converts a URL string value for a filter into a value the the Request query can accept
   *
   * @param string $value
   * @return string|array
   */
  public function transformPathValueForViewsQuery($filter_value_string, $filter_data);

  /**
   * Converts the form submitted value for a filter into a string for the URL
   *
   * @param string|array $value
   * @return string
   */
  public function transformSubmittedValueForUrl($values);

  /**
   * Returns array of filter plugin ID strings that the filter handler targets
   *
   * @return array
   */
  public function getTargetedFilterPluginIds();
}
