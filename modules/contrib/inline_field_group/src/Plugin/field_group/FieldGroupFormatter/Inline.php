<?php

namespace Drupal\inline_field_group\Plugin\field_group\FieldGroupFormatter;

use Drupal\field_group\FieldGroupFormatterBase;

/**
 * Plugin implementation of the 'inline' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "inline",
 *   label = @Translation("Inline"),
 *   description = @Translation("This renders the inner content as inline elements."),
 *   supported_contexts = {
 *     "form",
 *     "view",
 *   }
 * )
 */
class Inline extends FieldGroupFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);
    $info = \Drupal::service('plugin.manager.element_info')->getInfo('inline_group');

    $element += [
      '#type' => 'inline_group',
      '#settings' => $this->getSettings(),
    ] + $info;

    $element['#attached']['library'] = $info['#attached']['library'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();
    $container = $this->getSetting('container');
    $gutter = $this->getSetting('gutter');

    $form['container'] = [
      '#type' => 'inline_group',
      '#title' => $this->t('Container'),
      '#weight' => 12,
      '#theme_wrappers' => ['fieldset'],
      '#settings' => [
        'gutter' => ['type' => 'auto'],
        'children' => [
          'no_wrap' => [
            'settings' => [
              'valign' => 'top',
              'width_type' => 'custom',
              'width_value' => '190px',
            ],
          ],
          'overflow' => [
            'settings' => [
              'valign' => 'top',
              'width_type' => 'custom',
              'width_value' => '190px',
            ],
          ],
          'mobile_stack' => [
            'settings' => [
              'valign' => 'top',
              'width_type' => 'custom',
              'width_value' => '190px',
            ],
          ],
        ],
      ],
    ];

    $form['container']['no_wrap'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('No Wrap'),
      '#description' => $this->t('Prevent child elements from wrapping to the next line.'),
      '#default_value' => $container['no_wrap'],
      '#attributes' => ['data-fieldgroup-selector' => 'no_wrap'],
    ];

    $form['container']['mobile_stack'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mobile Stack'),
      '#description' => $this->t('Disable no wrap on mobile.'),
      '#default_value' => $container['mobile_stack'],
      '#states' => [
        'visible' => [
          ':input[data-fieldgroup-selector="no_wrap"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['container']['overflow'] = [
      '#type' => 'select',
      '#options' => [
        'visible' => $this->t('Visible'),
        'hidden' => $this->t('Hidden'),
        'scroll' => $this->t('Scroll'),
      ],
      '#description' => $this->t('What happens to child elements when they exceed the container width.'),
      '#default_value' => $container['overflow'],
      '#states' => [
        'visible' => [
          ':input[data-fieldgroup-selector="no_wrap"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['gutter'] = [
      '#type' => 'inline_group',
      '#title' => $this->t('Gutter'),
      '#weight' => 14,
      '#theme_wrappers' => ['fieldset'],
      '#settings' => [
        'gutter' => ['type' => 'auto'],
        'children' => [
          'type' => [
            'settings' => [
              'valign' => 'top',
              'width_type' => 'auto',
            ],
          ],
          'size' => [
            'settings' => [
              'valign' => 'top',
              'width_type' => 'auto',
            ],
          ],
        ],
      ],
    ];

    $form['gutter']['type'] = [
      '#type' => 'select',
      '#options' => [
        'default' => $this->t('Default'),
        'auto' => $this->t('Auto'),
        'custom' => $this->t('Custom'),
      ],
      '#description' => $this->t('Space between inline elements.'),
      '#default_value' => $gutter['type'],
      '#attributes' => ['data-fieldgroup-selector' => 'gutter_type'],
    ];

    $form['gutter']['size'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Length with unit: 5px, 5em, 5%, etc.'),
      '#size' => 9,
      '#default_value' => $gutter['size'],
      '#states' => [
        'visible' => [
          ':input[data-fieldgroup-selector="gutter_type"]' => ['value' => 'custom'],
        ],
        'required' => [
          ':input[data-fieldgroup-selector="gutter_type"]' => ['value' => 'custom'],
        ],
      ],
    ];

    // Customize the width of child elements.
    if (!empty($this->group->children)) {
      $children = $this->getSetting('children');

      $form['children'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Child Element'),
          $this->t('Settings'),
        ],
        '#weight' => 15,
        '#theme_wrappers' => ['fieldset'],
      ];

      // Create settings for each child element.
      foreach ($this->group->children as $field_name) {
        $form['children'][$field_name]['field_name'] = [
          '#markup' => $field_name,
        ];

        $form['children'][$field_name]['settings'] = [
          '#type' => 'inline_group',
          '#settings' => [
            'gutter' => ['type' => 'auto'],
            'children' => [
              'valign' => [
                'settings' => [
                  'valign' => 'top',
                  'width_type' => 'auto',
                ],
              ],
              'width_type' => [
                'settings' => [
                  'valign' => 'top',
                  'width_type' => 'auto',
                ],
              ],
              'width_value' => [
                'settings' => [
                  'valign' => 'top',
                  'width_type' => 'auto',
                ],
              ],
            ],
          ],
        ];

        $form['children'][$field_name]['settings']['valign'] = [
          '#type' => 'select',
          '#options' => [
            'baseline' => $this->t('Baseline'),
            'top' => $this->t('Top'),
            'middle' => $this->t('Middle'),
            'bottom' => $this->t('Bottom'),
          ],
          '#description' => $this->t('Vertical aligment.'),
          '#default_value' => $children[$field_name]['settings']['valign'] ?? 'baseline',
        ];

        $form['children'][$field_name]['settings']['width_type'] = [
          '#type' => 'select',
          '#options' => [
            'default' => $this->t('Default'),
            'auto' => $this->t('Auto'),
            'custom' => $this->t('Custom'),
          ],
          '#description' => $this->t('Width type.'),
          '#default_value' => $children[$field_name]['settings']['width_type'] ?? 'default',
          '#attributes' => ['data-fieldgroup-selector' => $field_name . '_width_type'],
        ];

        $form['children'][$field_name]['settings']['width_value'] = [
          '#type' => 'textfield',
          '#description' => $this->t('Length with unit: 5px, 5em, 5%, etc.'),
          '#size' => 9,
          '#default_value' => $children[$field_name]['settings']['width_value'] ?? '',
          '#states' => [
            'visible' => [
              ':input[data-fieldgroup-selector="' . $field_name . '_width_type"]' => ['value' => 'custom'],
            ],
            'required' => [
              ':input[data-fieldgroup-selector="' . $field_name . '_width_type"]' => ['value' => 'custom'],
            ],
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('no_wrap')) {
      $summary[] = $this->t('No wrap');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context) {
    return [
      'container' => [
        'no_wrap' => 0,
        'mobile_stack' => 0,
        'overflow' => 'visible',
      ],
      'gutter' => [
        'type' => 'default',
        'size' => '',
      ],
      'children' => [],
    ] + parent::defaultSettings($context);
  }

}
