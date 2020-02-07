<?php

namespace Drupal\inline_field_group\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for inline group.
 *
 * @FormElement("inline_group")
 */
class InlineGroup extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#pre_render' => [
        [$class, 'preRenderInlineGroup'],
      ],
      '#class_name' => 'inline-group',
      '#attributes' => ['class' => ['inline-group']],
      '#attached' => ['library' => ['inline_field_group/formatter.inline']],
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Pre render element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   inline group.
   *
   * @return array
   *   The modified element.
   */
  public static function preRenderInlineGroup(array $element) {
    $class_name = $element['#class_name'];
    $no_wrap = !empty($element['#settings']['container']['no_wrap']);
    $gutter_type = $element['#settings']['gutter']['type'] ?? 'default';
    $gutter_value = $element['#settings']['gutter']['size'] ?? '';

    // Inner container.
    $element['content'] = [
      '#type' => 'container',
      '#attributes' => ['class' => [$class_name . '__content']],
    ];

    // Prevent child elements from wrapping to the next line.
    if ($no_wrap) {
      $element['content']['#attributes']['class'][] = 'no-wrap';
      $element['#attributes']['class'][] = 'overflow-' . ($element['#settings']['container']['overflow'] ?? 'visible');

      // Disable no wrap on mobile.
      if (!empty($element['#settings']['container']['mobile_stack'])) {
        $element['content']['#attributes']['class'][] = 'mobile-stack';
      }
    }

    // Gutter type class.
    if ($gutter_type != 'default') {
      $element['content']['#attributes']['class'][] = 'gutter-' . $gutter_type;
    }

    // Custom gutter values.
    if ($gutter_type == 'custom') {
      $element['content']['#attributes']['style'] = [
        "margin-left: -${gutter_value};",
        "margin-right: -${gutter_value};",
      ];
    }

    // Process child elements.
    foreach (Element::getVisibleChildren($element) as $key) {
      // Skip content container.
      if ($key == 'content') {
        continue;
      }

      $is_form_element = (!empty($element[$key]['#theme_wrappers']) && in_array('form_element', $element[$key]['#theme_wrappers']));
      $attr_key = $is_form_element ? '#wrapper_attributes' : '#attributes';

      // Add class to child element.
      $element[$key][$attr_key]['class'][] = $class_name . '__item';

      // Add class based on cardinality.
      if (isset($element[$key]['widget']['#cardinality'])) {
        $cardinality = $element[$key]['widget']['#cardinality'];
        $element[$key][$attr_key]['class'][] = 'cardinality-' . (($cardinality == -1) ? 'unlimited' : $cardinality);
      }

      if (isset($element['#settings']['children'][$key]['settings'])) {
        $field_settings = $element['#settings']['children'][$key]['settings'];
        $width_type = $field_settings['width_type'] ?? 'default';

        // Width type class.
        if ($width_type != 'default') {
          $element[$key][$attr_key]['class'][] = 'width-' . $width_type;
        }

        // Custom width values.
        if ($width_type == 'custom' && !empty($field_settings['width_value'])) {
          $width_value = $field_settings['width_value'];

          if ($no_wrap) {
            $element[$key][$attr_key]['style'] = [
              "max-width: ${width_value};",
              "min-width: ${width_value};",
            ];
          }

          else {
            $element[$key][$attr_key]['style'] = ["width: ${width_value};"];
          }
        }

        // Vertical alignment.
        if (!empty($field_settings['valign'])) {
          $element[$key][$attr_key]['class'][] = 'valign-' . $field_settings['valign'];
        }
      }

      // Custom gutter values.
      if ($gutter_type == 'custom') {
        $element[$key][$attr_key]['style'][] = "padding: 0 ${gutter_value};";
      }

      // Move element to content container.
      $element['content'][$key] = $element[$key];
      unset($element[$key]);
    }

    return $element;
  }

}
