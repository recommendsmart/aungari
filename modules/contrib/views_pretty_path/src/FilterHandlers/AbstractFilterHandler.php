<?php

namespace Drupal\views_pretty_path\FilterHandlers;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Database\Connection;

abstract class AbstractFilterHandler implements ViewsPrettyPathFilterHandlerInterface {

  /**
   * Database service
   *
   * @var Connection
   */
  protected $database;

  public function __construct(Connection $Connection) {
    $this->database = $Connection;
  }

  /**
   * @inheritDoc
   */
  public function getTargetedFilterPluginIds() {
  }
  public function transformPathValueForViewsQuery($filter_value_string, $filter_data) {
  }
  public function transformSubmittedValueForUrl($value) {
  }

  /**
   * Encode multiple words
   *
   * @param string $words_string
   * @return string
   */
  protected function encodeMultipleWordsForUrl($words_string, $delimiter = '-') {
    $words_array = explode(' ', $words_string);
    $encoded_words_array = array_map(function($word) {return $this->encodeWordForUrl($word);}, $words_array);
    return implode($delimiter, $encoded_words_array);
  }

  /**
   * Encode a single word
   *
   * @param string $word
   * @return string
   */
  protected function encodeWordForUrl($word) {
    $processed_word = str_replace('/', '%2F', $word); // Encode slash.
    $processed_word = trim(strtolower($processed_word));
    $processed_word = implode('-',array_map(function($word_item) { return UrlHelper::encodePath($word_item); }, explode('-', $processed_word)));
    return $processed_word;
  }

  /**
   * Decode a single word
   *
   * @param string $word
   * @return string
   */
  protected function decodeUrlWord($word) {
    $word = str_replace('%252f', '%2F', str_replace('%252F', '%2F', $word)); // Decode slash.
    return urldecode($word);
  }
}
