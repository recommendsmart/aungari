<?php

/**
 * @file
 * Contains vendor.page.inc.
 *
 * Page callback for Vendor entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Vendor templates.
 *
 * Default template: vendor.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_vendor(array &$variables) {
  // Fetch Vendor Entity Object.
  $vendor = $variables['elements']['#vendor'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
