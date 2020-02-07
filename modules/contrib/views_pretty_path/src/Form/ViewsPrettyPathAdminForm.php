<?php

namespace Drupal\views_pretty_path\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ViewsPrettyPathAdminForm.
 */
class ViewsPrettyPathAdminForm extends ConfigFormBase {

  /**
   * Property: Number of path items in form
   *
   * @var integer
   */
  protected $pathItemTotal = 1;

  /**
   * Temporary config, to be used by the Remove button.
   *
   * @var array
   */
  protected $tempPathsConfig = [];

  /**
   * Item id to remove.
   *
   * @var integer
   */
  protected $itemToRemove;

  /**
   * Entity Type Manager
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alias storage
   *
   * @var AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * Language manager
   *
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Views data
   *
   * @var array
   *   Keyed by view ID, valued by view label.
   */
  protected $viewsData;

  /**
   * Form state
   *
   * @var FormStateInterface
   */
  protected $formState = NULL;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $EntityTypeManager, AliasStorageInterface $AliasStorage, LanguageManagerInterface $LanguageManager, RequestStack $RequestStack) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $EntityTypeManager;
    $this->viewsData = $this->loadViewData();
    $this->aliasStorage = $AliasStorage;
    $this->languageManager = $LanguageManager;
    $this->tempPathsConfig = $this->config('views_pretty_path.config')->get('paths');
    $this->request = $RequestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('path.alias_storage'),
      $container->get('language_manager'),
      $container->get('request_stack')
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'views_pretty_path.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'views_pretty_path_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (is_null($this->formState)) {
      $this->formState = $form_state;
    }
    // Get config.
    $paths = $this->tempPathsConfig;
    // Make sure to use tree.
    $form['#tree'] = TRUE;
    // Disable caching on this form.
    $form_state->setCached(FALSE);
    // Get number of paths already loaded into config.
    if (!empty($paths) && !$form_state->get('ajax_pressed')) {
      $this->pathItemTotal = count($paths) > 0 ? count($paths) : 1;
    }
    // Build paths container.
    $form['paths'] = [
      '#type' => 'table',
      '#header' => [
          $this->t('Path to Rewrite'),
          $this->t('View'),
          $this->t('Display'),
          '',
      ],
      '#empty' => $this->t('No privileges.'),
      '#tableselect' => FALSE,
      '#attributes' => ['id' => 'paths-container'],
    ];
    for ($i = 0; $i < $this->pathItemTotal; $i++) {
      $form['paths'][$i] = [
        '#type'       => 'fieldset',
      ];
      // Path.
      $form['paths'][$i]['path'] = [
        '#type'       => 'textfield',
        '#attributes' => ['placeholder' => $this->t('Path to rewrite')],
        '#size'       => 50,
        '#required' => TRUE,
        '#default_value' => empty($paths[$i]['path']) ? '' : $paths[$i]['path'],
      ];
      // View.
      $form['paths'][$i]['view'] = [
        '#type'       => 'select',
        '#options' => $this->viewsData,
        '#default_value' => empty($paths[$i]['view']) ? null : $paths[$i]['view'],
        '#required' => TRUE,
        '#ajax' => [
          'callback' => [$this, 'selectView'],
          'event' => 'change',
          'wrapper' => 'paths-container',
        ],
      ];
      // Build view display form element, if view is selected.
      if (!empty($paths[$i]['view'])) {
        $selected_view_id = $paths[$i]['view'];
      }
      elseif (!empty($form_state->getUserInput()['paths'][$i]['view'])) {
        $selected_view_id = $form_state->getUserInput()['paths'][$i]['view'];
      }
      if (isset($selected_view_id)) {
        $form['paths'][$i]['display'] = [
          '#type'       => 'select',
          //'#title' => $this->t('Display'),
          '#required' => TRUE,
          '#options' => $this->getViewDisplays($selected_view_id),
          '#default_value' => empty($paths[$i]['display']) ? 'default' : $paths[$i]['display'],
        ];
      }
      else {
        $form['paths'][$i]['display'] = [
          '#type'       => 'markup',
          '#markup' => '',
        ];
      }
      unset($selected_view_id);
      // Remove button.
      $form['paths'][$i]['remove_item_' . $i] =[
        '#type'                    => 'submit',
        '#name'                    => 'remove_' . $i,
        '#value'                   => $this->t('Remove'),
        '#submit'                  => ['::removeItem'],
        // Since we are removing an item, don't validate until later.
        '#limit_validation_errors' => [],
        '#ajax'                    => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper'  => 'paths-container',
        ],
      ];
    }
    // Add item button.
    $form['paths']['actions'] = [
      '#type' => 'actions',
      'add_item' => [
        '#type'   => 'submit',
        '#value'  => $this->t('Add a new path to rewrite'),
        '#submit' => ['::addItem'],
        '#ajax'   => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper'  => 'paths-container',
        ],
      ]
    ];
    $form['note'] = [
      '#type' => 'markup',
      '#markup' => $this->t('(Note: Choose a view the filters of which should be used in rewriting. The view must be displayed on the above path. This module can target only one view per path.)'),
    ];
    // Views filter-name mapping.
    $form['views_filter_name_map'] = [
      '#type' => 'textarea',
      '#attributes' => ['placeholder' => "Examples: \n\nfield_topic_target_id|topics\nfield_start_date_value|date"],
      '#title' => $this->t('Views Filter Identifier-Name Mapping'),
      '#description' => $this->t('Simplify views filter identifiers by mapping them to a special name. Use the following syntax, each rule separated by a new line: filter_identifier|name'),
      '#rows' => 5,
      '#default_value' => $this->config('views_pretty_path.config')->get('views_filter_name_map'),
    ];
    // Filter subpath.
    $form['filter_subpath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filter Subpath'),
      '#description' => $this->t('What should the subpath deliniating filtering be called (i.e. &#039;/filter/{term1}/{term2}&#039; has the subpath of &#039;/filter&#039;)?'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $this->config('views_pretty_path.config')->get('filter_subpath'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (isset($values['paths'])) {
      $path_collector = [];
      foreach ($values['paths'] as $key => $path_value) {
        // If alias doesn't exist, consider setting a form error.
        if (is_numeric($key) && !$this->aliasStorage->aliasExists($path_value['path'], $this->languageManager->getDefaultLanguage()->getId())) {
          $set_error = TRUE;
          // Check to make sure the selected view display isn't a page with that path.
          if ((!empty($path_value['view'])) && ($view = $this->entityTypeManager->getStorage('view')->load($path_value['view']))) {
            if (
              !empty($view->getDisplay($path_value['display'])['display_options']['path']) &&
              $view->getDisplay($path_value['display'])['display_options']['path'] == ltrim($path_value['path'], '/')
            ) {
              $set_error = FALSE;
            }
          }
          if ($set_error) {
            $form_state->setError($form['paths'][$key]['path'], $this->t('The path provided does not exist in the system.'));
          }
        }
        // Make sure the user only selects one view per path.
        if (is_numeric($key)) {
          if (in_array($path_value['path'], $path_collector)) {
            $form_state->setError($form['paths'][$key]['path'], $this->t('You cannot rewrite the path, @path, more than once.', ['@path' => $path_value['path']]));
          }
          $path_collector[] = $path_value['path'];
        }
      }
    }

  }

  /**
   * Load views data
   *
   * @return array
   *   Keyed by View ID, valued by View label.
   */
  protected function loadViewData() {
    return array_map(function($view) {
      return $view->label();
    }, $this->entityTypeManager->getStorage('view')->loadMultiple());
  }

  /**
   * Get display data for a view id
   *
   * @param string $view_id
   * @return array
   */
  protected function getViewDisplays($view_id) {
    $view = $this->entityTypeManager->getStorage('view')->load($view_id);
    return array_map(function($display) {
      return $display['display_title'];
    }, $view->get('display'));
  }

  /**
   * Implements callback for Ajax event
   *
   * @param array $form
   *   From render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Container section of the form.
   */
  public function ajaxCallback($form, $form_state) {
    // Set new values if remove was pressed.
    if ($this->getCurrentRequestVariable('remove_pressed')) {
      // Get input values;
      $values = $form_state->getUserInput();
      // Remove the removed item;
      unset($values['paths'][$this->itemToRemove]);
      $values['paths'] = array_combine(range(0, count($values['paths']) - 1), array_values($values['paths']));
      // Set new values;
      for ($i = 0; $i < $this->pathItemTotal; $i++) {
        $form['paths'][$i]['path']['#value'] = empty($values['paths'][$i]['path']) ? '' : $values['paths'][$i]['path'];
        $form['paths'][$i]['view']['#value'] = empty($values['paths'][$i]['view']) ? '' : $values['paths'][$i]['view'];
        $form['paths'][$i]['display']['#value'] = empty($values['paths'][$i]['display']) ? '' : $values['paths'][$i]['display'];
      }
    }
    $this->setCurrentRequestVariable('remove_pressed', FALSE);
    return $form['paths'];
  }

  /**
   * Adds an item to form
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function selectView(array &$form, FormStateInterface $form_state) {
    $form_state->set('ajax_pressed', TRUE);
    return $this->ajaxCallback($form, $form_state);
  }

  /**
   * Adds an item to form
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function addItem(array &$form, FormStateInterface $form_state) {
    $form_state->set('ajax_pressed', TRUE);
    $this->pathItemTotal++;
    $form_state->setRebuild();
  }

  /**
   * Removes an item from form
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function removeItem(array &$form, FormStateInterface $form_state) {
    $form_state->set('ajax_pressed', TRUE);
    $this->setCurrentRequestVariable('remove_pressed', TRUE);
    $this->pathItemTotal--;
    // Get triggering item id;
    $triggering_element = $form_state->getTriggeringElement();
    preg_match_all('!\d+!', $triggering_element['#name'], $matches);
    $item_id = (int) $matches[0][0];
    $this->itemToRemove = $item_id;
    // Remove item from config, reindex at 1, and set tempPathsConfig to it.
    unset($this->tempPathsConfig[$item_id]);
    $this->tempPathsConfig = array_combine(range(0, count($this->tempPathsConfig) - 1), array_values($this->tempPathsConfig));
    // Rebuild form;
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // add to config
    $paths_values =  $form_state->getValue('paths');
    unset($paths_values['actions']);
    foreach ($paths_values as $key => &$value) {
      unset($value['remove_item_' . $key]);
    }
    $this->config('views_pretty_path.config')
      ->set('filter_subpath', $form_state->getValue('filter_subpath'))
      ->set('views_filter_name_map', $form_state->getValue('views_filter_name_map'))
      ->set('paths', $paths_values)
      ->save();
  }

  /**
   * Set volatile variable, specific to current request time
   *
   * @param string $name
   * @param mixed $value
   */
  protected function setCurrentRequestVariable($name, $value) {
    $vars_identifier = sha1($this->request->getCurrentRequest()->server->get('REQUEST_TIME'));
    $vars = $this->formState->get($vars_identifier) ? $this->formState->get($vars_identifier) : [];
    $vars[$name] = $value;
    $this->formState->set($vars_identifier, $vars);
  }

  /**
   * Get volatile variable, specific to current request time
   *
   * @param mixed|null $name
   */
  protected function getCurrentRequestVariable($name) {
    $vars_identifier = sha1($this->request->getCurrentRequest()->server->get('REQUEST_TIME'));
    if (($vars = $this->formState->get($vars_identifier)) && isset($vars[$name])) {
      return $vars[$name];
    }
    return NULL;
  }

}
