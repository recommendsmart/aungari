<?php

namespace Drupal\skins\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use  Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Callbacks for the system_theme_seettings form.
 */
class ThemeSettings implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderSkinRadios'];
  }

  /**
   * #pre_render callback: Adds images and descriptions to radio elements.
   */
  public static function preRenderSkinRadios($element) {
    $render_service = \Drupal::service('renderer');
    // Process the empty (none) option.
    $screenshot = [
      '#theme' => 'image',
      '#uri' => $element['#theme_screenshot_uri'],
      '#alt' => t('Default theme screenshot'),
      '#title' => t('Default theme screenshot'),
      '#attributes' => new Attribute(['class' => ['no-screenshot']]),
    ];
    $element['']['#prefix'] = '<div class="theme-selector">' . $render_service->render($screenshot);
    $element['']['#title'] = '<strong>' . $element['']['#title'] . '</strong>';
    $element['']['#suffix'] = '<div class="clearfix">' . t('Use the theme\'s default styling.') . '</div></div>';

    // Process each skin option.
    foreach ($element['#skins'] as $skin_id => $skin) {
      $wrapper_classes = [
        'theme-selector',
      ];
      if ($skin_id === $element['#default_value']) {
        $wrapper_classes[] = 'theme-default';
      }
      $screenshot = [
        '#theme' => 'image',
        '#uri' => drupal_get_path($skin['provider_type'], $skin['provider']) . '/' . $skin['screenshot'],
        '#alt' => t('Skin preview: @skin_id', ['@skin_id' => $skin['name']]),
        '#title' => t('Skin preview: @skin_id', ['@skin_id' => $skin['name']]),
        '#attributes' => new Attribute(['class' => ['screenshot']]),
      ];
      $element[$skin_id]['#title'] = '<strong>' . $element[$skin_id]['#title'] . '</strong>';
      $element[$skin_id]['#prefix'] = '<div class="' . implode(' ', $wrapper_classes) . '">' . $render_service->render($screenshot);
      $element[$skin_id]['#suffix'] = '<div class="clearfix">' . $skin['description'] . '</div></div>';
    }
    return $element;
  }

  /**
   * Form submit callback: resets system information for themes.
   */
  public static function submit(array &$form, FormStateInterface $form_state) {
    // Only clear caches if the selected skin has changed.
    if ($form['skins']['skin']['#default_value'] !== $form_state->getValues()['skin']) {
      // Reset the list of themes.
      // Need to call ::getList() so that the data exist prior to the form being
      // rebuilt.
      \Drupal::service('extension.list.theme')
        ->reset()
        ->getList();

      // Invalidate the library_info cache.
      Cache::invalidateTags(['library_info']);

      // Invalidate the render cache so that new template suggestions will be used.
      \Drupal::service('cache.render')->invalidateAll();

      // Invalidate the Twig cache.
      \Drupal::service('twig')->invalidate();

      // Flush the CSS cache.
      \Drupal::service('asset.css.collection_optimizer')->deleteAll();

      // Reset the theme registry.
      \Drupal::service('theme.registry')->reset();
    }

  }

}
