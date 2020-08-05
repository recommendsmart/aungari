<?php

namespace Drupal\skins;

use Drupal\skins\Event\SkinsEvents;
use Drupal\skins\Event\SkinsTransformEvent;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides the available skins based on yml files.
 *
 * To define skins you can use a $theme.skins.yml file. This file defines
 * machine names, human-readable names, style sheets, template directories,
 * and screenshot files.
 */
class SkinHandler implements SkinHandlerInterface {

  use StringTranslationTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The YAML discovery classes to find all .skins.yml files.
   *
   * @var \Drupal\Core\Discovery\YamlDiscovery[]
   */
  protected $yamlDiscovery = [];

  /**
   * Constructs a new SkinHandler.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, EventDispatcherInterface $event_dispatcher, TranslationInterface $string_translation) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->eventDispatcher = $event_dispatcher;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Gets the YAML discovery.
   *
   * @param string $extension_type
   *   The extension type, either 'module' or 'theme'.
   *
   * @return \Drupal\Core\Discovery\YamlDiscovery
   *   The YAML discovery.
   */
  protected function getYamlDiscovery($extension_type) {
    if (!isset($this->yamlDiscovery[$extension_type])) {
      switch ($extension_type) {
        case 'module':
          $directories = $this->moduleHandler->getModuleDirectories();
          break;
        case 'theme':
          $directories = $this->themeHandler->getThemeDirectories();
          break;
      }
      $this->yamlDiscovery[$extension_type] = new YamlDiscovery('skins', $directories);
    }

    return $this->yamlDiscovery[$extension_type];
  }

  /**
   * {@inheritdoc}
   */
  public function getSkins() {
    static $all_skins;

    if (!isset($all_skins)) {
      $all_skins = $this->buildSkinsYaml();
      $all_skins = $this->sortSkins($all_skins);
    }

    return $all_skins;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeSkins($theme_name) {
    $all_skins = $this->getSkins();

    $theme_skins = array_filter($all_skins, function($skin) use ($theme_name) {
      // A skin applies to a theme if either it doesn't specify themes or
      // the theme is one it specifies.
      return empty($skin['themes']) || in_array($theme_name, $skin['themes']);
    });

    return $theme_skins;
  }

  /**
   * {@inheritdoc}
   */
  public function themeHasSkins($theme_name) {
    $theme_skins = $this->getThemeSkins($theme_name);

    return !empty($theme_skins);
  }

  /**
   * Builds all skins provided by .skins.yml files.
   *
   * @return array[]
   *   An array of skins.
   */
  protected function buildSkinsYaml() {
    $all_skins = [];

    foreach (['module', 'theme'] as $extension_type) {
      foreach ($this->getYamlDiscovery($extension_type)->findAll() as $provider => $skins) {
        foreach ($skins as $skin_id => $skin) {
          $skin['name'] = $this->t($skin['name']);
          $skin['description'] = isset($skin['description']) ? $this->t($skin['description']) : NULL;
          $skin['provider'] = $skin['provider'] ?? $provider;
          $skin['provider_type'] = $extension_type;
          if (!isset($skin['themes'])) {
            $skin['themes'] = [];
            // If it's a theme, it applies to itself.
            if ($skin['provider_type'] === 'theme') {
              $skin['themes'][] = $skin['provider'];
            }
          }

          $all_skins[$skin_id] = $skin;
        }
      }
    }

    $this->eventDispatcher->dispatch(SkinsEvents::TRANSFORM_BUILD, new SkinsTransformEvent($all_skins));

    return $all_skins;
  }

  /**
   * Sorts the given skins by provider name and skin name.
   *
   * @param array $skins
   *   The skins to be sorted.
   *
   * @return array[]
   *   An array of skins.
   */
  protected function sortSkins(array $all_skins = []) {
    // Get a list of all the extensions providing skins and sort by
    // display name.
    $names = $this->getExtensionNames();

    uasort($all_skins, function (array $skin_a, array $skin_b) use ($names) {
      if ($names[$skin_a['provider_type']][$skin_a['provider']] == $names[$skin_b['provider_type']][$skin_b['provider']]) {
        return $skin_a['name'] > $skin_b['name'];
      }
      else {
        return $names[$skin_a['provider_type']][$skin_a['provider']] > $names[$skin_b['provider_type']][$skin_b['provider']];
      }
    });

    return $all_skins;
  }

  /**
   * Returns all extension names.
   *
   * @return array
   *   Returns a nested array of extension types and the human readable names
   *   of all extensions keyed by machine name.
   */
  protected function getExtensionNames() {
    $names = [
      'module' => [],
      'theme' => [],
    ];
    foreach (array_keys($this->moduleHandler
      ->getModuleList()) as $module) {
      $names['module'][$module] = $this->moduleHandler
        ->getName($module);
    }
    asort($names['module']);

    foreach (array_keys($this->themeHandler
      ->listInfo()) as $theme) {
      $names['theme'][$theme] = $this->themeHandler->getName($theme);
    }
    asort($names['theme']);

    return $names;
  }

}
