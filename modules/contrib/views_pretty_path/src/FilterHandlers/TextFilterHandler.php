<?php

namespace Drupal\views_pretty_path\FilterHandlers;

class TextFilterHandler extends AbstractFilterHandler implements ViewsPrettyPathFilterHandlerInterface {

  /**
   * @inheritDoc
   */
  public function getTargetedFilterPluginIds() {
    return [
      'search_keywords',
      'search_api_fulltext',
    ];
  }

  /**
   * @inheritDoc
   */
  public function transformPathValueForViewsQuery($filter_value_string, $filter_data) {
    $raw_values = explode('+', $filter_value_string);
    $return_text = '';
    if (!empty($raw_values)) {
      foreach ($raw_values as $raw_value) {
        $return_text = $return_text . ' ' . $this->decodeUrlWord($raw_value);
      }
      $return_text = ltrim($return_text);
    }
    return $return_text;
  }

  /**
   * @inheritDoc
   */
  public function transformSubmittedValueForUrl($value) {
    return $this->encodeMultipleWordsForUrl($value, '+');
  }
}
