<?php

/**
 * @file
 * Contains branch.page.inc.
 *
 * Page callback for Branch entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Branch templates.
 *
 * Default template: branch.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_branch(array &$variables) {
  // Fetch Branch Entity Object.
  $branch = $variables['elements']['#branch'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
