<?php

namespace Drupal\advance_link_attributes\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Plugin implementation of the 'advance_link_attributes_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "advance_link_attributes_field_widget",
 *   label = @Translation("Advance Link Attributes"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class AdvanceLinkAttributesFieldWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'ala_link_class_settings' => '',
      'ala_link_class' => '',
      'ala_link_icon' => '',
      'ala_link_roles' => 'all',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $item = $this->getLinkItem($items, $delta);
    $options = $item->get('options')->getValue();

    $targets_available = [
      '_self' => 'Current window (_self)',
      '_blank' => 'New window (_blank)',
      'parent' => 'Parent window (_parent)',
      'top' => 'Topmost window (_top)',
    ];
    $default_value = !empty($options['attributes']['target']) ? $options['attributes']['target'] : '';
    $element['options']['attributes']['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a target'),
      '#options' => ['' => $this->t('- None -')] + $targets_available,
      '#default_value' => $default_value,
      '#description' => $this->t('Select a link behavior. <em>_self</em> will open the link in the current window. <em>_blank</em> will open the link in a new window or tab. <em>_parent</em> and <em>_top</em> will generally open in the same window or tab, but in some cases will open in a different window.'),
    ];

    if (($this->getSetting('ala_link_icon'))) {
      $icon = !empty($options['icon']) ? $options['icon'] : '';
      $element['options']['icon'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Icon Class'),
        '#default_value' => $icon,
        '#description' => $this->t('Icon Class, fal fa-icon'),
      ];
    }

    if ($this->getSetting('ala_link_roles')) {
      $roles = Role::loadMultiple();
      $system_roles = array_map(
        function (RoleInterface $a) {
          return $a->label();
        }, $roles);

      $default_value = !empty($options['roles']) ? $options['roles'] : '';
      $element['options']['roles'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Visible for'),
        '#options' => [
          'all' => $this->t('- Everyone -'),
          'authenticated' => $this->t('- Logged -'),
        ] + $system_roles,
        '#default_value' => $default_value,
      ];
    }

    $class_settings = $this->getSetting('ala_link_class_settings');
    if (!empty($class_settings)) {

      switch ($class_settings) {
        case 'global':
          $config = Drupal::config('advance_link_attributes.settings');
          $classes_available = $this->getSelectOptions($config->get('ala_default_classes'));
          break;

        case 'custom':
          $classes_available = $this->getSelectOptions($this->getSetting('ala_link_class'));
          break;

        default:
          $classes_available = [];
          break;
      }

      $default_value = !empty($options['class']) ? $options['class'] : '';
      $element['options']['class'] = [
        '#type' => 'select',
        '#title' => $this->t('Select a style'),
        '#options' => ['' => $this->t('- None -')] + $classes_available,
        '#default_value' => $default_value,
      ];
    }

    return $element;
  }

  /**
   * Getting link items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Returning of field items.
   * @param string $delta
   *   Returning field delta with item.
   *
   * @return \Drupal\link\LinkItemInterface
   *   Returning link items inteface.
   */
  private function getLinkItem(FieldItemListInterface $items, $delta) {
    return $items[$delta];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['ala_link_class_settings'] = [
      '#type' => 'select',
      '#title' => $this->t('Class Settings'),
      '#default_value' => $this->getSetting('ala_link_class_settings'),
      '#options' => [
        '' => $this->t('Disabled'),
        'global' => $this->t('Global List'),
        'custom' => $this->t('Custom List'),
      ],
    ];

    $element['ala_link_class'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Define possibles classes'),
      '#default_value' => $this->getSetting('ala_link_style'),
      '#description' => $this->selectClassDescription(),
      '#attributes' => [
        'placeholder' => 'btn btn-default|Default button' . PHP_EOL . 'btn btn-primary|Primary button',
      ],
      '#size' => '30',
      '#states' => [
        'visible' => [
          [
            [':input[name="fields[field_link][settings_edit_form][settings][ala_link_class_settings]"]' => ['value' => 'custom']],
          ],
        ],
      ],
    ];

    $element['ala_link_icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Icon'),
      '#default_value' => $this->getSetting('ala_link_icon'),
    ];
    $element['ala_link_roles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable User Roles'),
      '#default_value' => $this->getSetting('ala_link_roles'),
    ];

    return $element;
  }

  /**
   * Return the description for the class select mode.
   */
  protected function selectClassDescription() {
    $description = '<p>' . $this->t('The possible classes this link can have. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . $this->t('The key is the string which will be used as a class on a link. The label will be used on edit forms.');
    $description .= '<br/>' . $this->t('If the key contains several classes, each class must be separated by a <strong>space</strong>.');
    $description .= '<br/>' . $this->t('The label is optional: if a line contains a single string, it will be used as key and label.');
    $description .= '</p>';
    return $description;
  }

  /**
   * Convert textarea lines into an array.
   *
   * @param string $string
   *   The textarea lines to explode.
   * @param bool $summary
   *   A flag to return a formatted list of classes available.
   *
   * @return array
   *   An array keyed by the classes.
   */
  protected function getSelectOptions($string, $summary = FALSE) {
    $options = [];
    $lines = preg_split("/\\r\\n|\\r|\\n/", trim($string));
    $lines = array_filter($lines);

    foreach ($lines as $line) {
      list($class, $label) = explode('|', trim($line));
      $label = $label ?: $class;
      $options[$class] = $label;
    }

    if ($summary) {
      return implode(', ', array_keys($options));
    }
    return $options;
  }

}
