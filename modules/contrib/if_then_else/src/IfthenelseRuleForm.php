<?php

namespace Drupal\if_then_else;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\if_then_else\Event\EventConditionEvent;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else\Event\SocketSubscriptionEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function GuzzleHttp\json_decode;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines a class to build a ifthenelseRule entity form.
 *
 * @see \Drupal\if_then_else
 */
class IfthenelseRuleForm extends EntityForm {

  /**
   * The Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The module manager.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs an ExampleForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The entityTypeManager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EventDispatcherInterface $event_dispatcher, ModuleHandlerInterface $moduleHandler) {
    $this->entityTypeManager = $entityTypeManager;
    $this->eventDispatcher = $event_dispatcher;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['#prefix'] = '<div id="ifthenelse_form_wrapper">';
    $form['#suffix'] = '</div>';

    $socket_subscription_event = new SocketSubscriptionEvent();
    $this->eventDispatcher->dispatch(SocketSubscriptionEvent::EVENT_NAME, $socket_subscription_event);
    $form['#attached']['drupalSettings']['if_then_else']['sockets'] = $socket_subscription_event->sockets;

    // Current entity object.
    $entity = $this->entity;

    // Instantiate our event.
    $event = new NodeSubscriptionEvent();

    // Get the event_dispatcher server and dispatch the event.
    $this->eventDispatcher->dispatch(NodeSubscriptionEvent::EVENT_NAME, $event);

    // Adding retejs intialization library.
    $form['#attached']['library'][] = 'if_then_else/initialize_retejs';
    $form['#attached']['library'][] = 'if_then_else/if_then_else';

    // Attach all libraries defined by all modules which are subscribing
    // above event.
    foreach ($event->nodes as $node_name => $node) {
      if (!empty($node['dependencies'])) {
        $exist = FALSE;
        foreach ($node['dependencies'] as $module) {
          if (!$this->moduleHandler->moduleExists($module)) {
            $exist =TRUE;
          }
        }
        if (!empty($exist)){
          continue;
        }
      }
      $node['name'] = $node_name;

      if (isset($node['library'])) {
        $form['#attached']['library'][] = $node['library'];
        unset($node['library']);
      }

      if ($node['type'] != 'event') {
        // Check if 'execute' input is defined.
        if (array_key_exists('inputs', $node) && array_key_exists('execute', $node['inputs'])) {
          // Make 'execute' key the first one.
          $execute['execute'] = $node['inputs']['execute'];
          unset($node['inputs']['execute']);
        }
        else {
          $execute['execute'] = [
            'label' => $this->t('Execute'),
            'description' => $this->t('Should the @type be executed?', ['@type' => $node['type']]),
            'sockets' => ['bool'],
          ];
        }

        if (array_key_exists('inputs', $node)) {
          $node['inputs'] = $execute + $node['inputs'];
        }
        else {
          $node['inputs'] = $execute;
        }
      }

      if ($node['type'] == 'action' || $node['type'] == 'event') {
        if (array_key_exists('outputs', $node) && array_key_exists('success', $node['outputs'])) {
          // Make 'success' key the first one.
          $success['success'] = $node['outputs']['success'];
          unset($node['outputs']['success']);
        }
        else {
          $success['success'] = [
            'label' => $this->t('Success'),
            'description' => ($node['type'] == 'action') ? $this->t("Did the action execute without any error? This socket can be used to chain actions by connecting to the next action's 'Execute' socket") : $this->t("This socket will always be TRUE for an event and can be used to connect to a condition or action's 'Execute' socket."),
            'socket' => 'bool',
          ];

          if (!array_key_exists('outputs', $node)) {
            $node['outputs'] = [];
          }
        }

        if (array_key_exists('outputs', $node)) {
          $node['outputs'] = $success + $node['outputs'];
        }
        else {
          $node['outputs'] = $success;
        }
      }

      $form['#attached']['drupalSettings']['if_then_else']['nodes'][$node_name] = $node;
    }

    $form['#attached']['library'][] = 'if_then_else/render_retejs_canvas';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#required' => TRUE,
      '#default_value' => $entity->label(),
      '#weight' => 10,
      '#prefix' => '<div class="container-inline label_checkbox_section">',
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$entity->isNew(),
      '#weight' => 15,
    ];

    $form['active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active'),
      '#default_value' => isset($entity->active) ? $entity->active : '',
      '#weight' => 20,
      '#suffix' => '</div>',
    ];
    $full_screen_img_path = drupal_get_path('module', 'if_then_else') . '/css/images/full_screen.png';
    $form['full_screen_button'] = [
      '#type' => 'image_button',
      '#title' => $this->t('Enable Full Screen'),
      '#src' => $full_screen_img_path,
      '#weight' => -1,
    ];
    // Container to create retejs editor and nodes.
    $form['rete-container'] = [
      '#type' => 'item',
      '#markup' => '<div class="editor"><div id="dock"></div><div class="container"><div id="rete-editor"></div></div></div>',
      '#weight' => -1,
    ];

    // Get values of usre input. It will be empty if form is not
    // submitted.
    $form_inputs = $form_state->getUserInput();
    if (!empty($form_inputs)) {
      $form['#attached']['drupalSettings']['if_then_else']['data'] = $form_inputs['data'];
    }
    else {
      $form['#attached']['drupalSettings']['if_then_else']['data'] = isset($entity->data) ? $entity->data : FALSE;
    }

    $form['module'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Module'),
      '#value' => 'ifthenelse',
      '#default_value' => isset($entity->module) ? $entity->module : '',
    ];

    // @todo: For now the event is hardcoded using javascript to form_alter
    // Need to make that dynamic based on what event node is added on retejs
    $form['event'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Module'),
      '#attributes' => [
        'id' => 'ifthenelse-event',
      ],
      '#default_value' => isset($entity->event) ? $entity->event : '',
    ];

    $form['data'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Module'),
      '#attributes' => [
        'id' => 'ifthenelse-data',
      ],
      '#default_value' => isset($entity->data) ? $entity->data : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if (!$this->entity->isNew()) {
      $actions['clone'] = [
        '#type' => 'link',
        '#title' => $this->t('Clone'),
        '#url' => $this->entity->toUrl('clone'),
        '#attributes' => ['class' => 'button button--danger', '#id' => 'test'],
      ];
    }

    return $actions;
  }

  /**
   * Validate form for ifthenelse rule entity.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // If the flow is not active, there is no need to validate.
    if (!$form_state->getValue('active')) {
      $form_state->setValue('processed_data', '');
      return;
    }

    // Instantiate our event.
    $event = new NodeSubscriptionEvent();

    // Get the event_dispatcher server and dispatch the event.
    $this->eventDispatcher->dispatch(NodeSubscriptionEvent::EVENT_NAME, $event);

    $data = json_decode($form_state->getValue('data'));

    $event_name = '';

    // Make sure that the graph has only one event node.
    foreach ($data->nodes as $nid => $node) {
      if ($node->data->type == 'event') {
        if (empty($event_name)) {
          $event_name = $node->data->name;
        }
        else {
          // There are two events in this graph. Set an error.
          $form_state->setErrorByName('rete-container', $this->t('The graph has at least two event nodes. A graph should have exactly one event node.'));
          return;
        }
      }
    }

    if (empty($event_name)) {
      $form_state->setErrorByName('rete-container', $this->t('The graph has no event node. Add one event node to indicate when the graph should execute.'));
      return;
    }

    $has_error = FALSE;

    // Validate each node.
    $node_errors = [];
    foreach ($data->nodes as $nid => $node) {
      $node_validation_event = new NodeValidationEvent($nid, $node);
      $this->eventDispatcher->dispatch('if_then_else_' . $node->data->name . '_node_validation_event', $node_validation_event);
      if (property_exists($node->data, 'dependencies') && !empty($node->data->dependencies)) {
        foreach ($node->data->dependencies as $module) {
          if (!$this->moduleHandler->moduleExists($module)) {
            $node_errors[] = $this->t("@module_name module is not enabled. Node @node_name can't be added.", ['@module_name' => ucfirst($module), '@node_name' => $node->data->name]);
          }
        }
      }
      if (!empty($node_validation_event->errors)) {
        $node_errors = array_merge($node_errors, (array) $node_validation_event->errors);
      }
    }

    if (!empty($node_errors)) {
      $content = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $node_errors,
        '#attributes' => ['class' => 'error-list ifthenelse error'],
        '#wrapper_attributes' => ['class' => 'container'],
      ];
      $form_state->setErrorByName('rete-container', $content);
      return;
    }

    $precedent_nids = [];
    $dependent_nids = [];
    $execution_order = [];

    $event_condition = '';

    $has_error = FALSE;

    // Get execution order of precedent and dependent nodes and event condition.
    // Show an error if a required input is not connected.
    foreach ($data->nodes as $nid => $node) {
      $inputs = $node->inputs;
      $outputs = $node->outputs;

      if ($node->data->type == 'event') {
        $event_condition_event = new EventConditionEvent($node->data);
        $this->eventDispatcher->dispatch('if_then_else_' . $node->data->name . '_event_condition_event', $event_condition_event);
        $event_condition = implode(',', $event_condition_event->conditions);
      }

      $defined_inputs = array_key_exists('inputs', $event->nodes[$node->data->name]) ? $event->nodes[$node->data->name]['inputs'] : [];
      foreach ($defined_inputs as $input_name => $defined_input) {
        if (in_array('required', $defined_input) && $defined_input['required'] && !count($node->inputs->{$input_name}->connections)) {
          // A required input is not connected.
          $form_state->setErrorByName('rete-container', $this->t('Required input "@input_name" of "@node_name" is not connected.', ['@input_name' => $defined_input['label'], '@node_name' => $node->name]));
        }
      }

      $dependent_nids[$nid] = [];
      $precedent_nids[$nid] = [];

      if (!empty((array) $inputs)) {
        foreach ($inputs as $socket_name => $input) {
          foreach ($input->connections as $precedent_node_output_object) {
            if (!in_array($precedent_node_output_object->node, $precedent_nids[$nid])) {
              $precedent_nids[$nid][] = $precedent_node_output_object->node;
            }
          }
        }
      }

      if (!empty((array) $outputs)) {
        foreach ($outputs as $socket_name => $output) {
          foreach ($output->connections as $dependent_node_input_object) {
            if (!in_array($dependent_node_input_object->node, $dependent_nids[$nid])) {
              $dependent_nids[$nid][] = $dependent_node_input_object->node;
            }
          }
        }
      }
    }

    if ($has_error) {
      return;
    }

    $form_state->setValue('event', $event_name);
    $form_state->setValue('condition', $event_condition);

    // Validate the full graph.
    $graph_errors = [];
    foreach ($data->nodes as $nid => $node) {
      $graph_validation_event = new GraphValidationEvent($data);
      $this->eventDispatcher->dispatch('if_then_else_' . $node->data->name . '_graph_validation_event', $graph_validation_event);
      if (!empty($graph_validation_event->errors)) {
        $graph_errors = array_merge($graph_errors, $graph_validation_event->errors);
      }
    }

    if (!empty($graph_errors)) {
      $content = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $graph_errors,
        '#attributes' => ['class' => 'error-list ifthenelse error'],
        '#wrapper_attributes' => ['class' => 'container'],
      ];
      $form_state->setErrorByName('rete-container', $content);
      return;
    }

    // Compute execution order of nodes.
    while (count($execution_order) < count((array) $data->nodes)) {
      $node_processed = FALSE;

      foreach ($data->nodes as $nid => $node) {
        if (in_array($nid, $execution_order)) {
          continue;
        }

        $all_inputs_available = TRUE;
        foreach ($precedent_nids[$nid] as $precedent_nid) {
          if (!in_array($precedent_nid, $execution_order)) {
            $all_inputs_available = FALSE;
            break;
          }
        }

        if (!$all_inputs_available) {
          continue;
        }

        // Found a node which could be processed now.
        if (isset($node->data->class)) {
          $class = str_replace('//', '/', $node->data->class);

          if (class_exists($class)) {
            $execution_order[] = $nid;
            $node_processed = TRUE;
          }
        }
      }

      if (!$node_processed) {
        // There seems to be circular dependency.
        $form_state->setErrorByName('rete-container', $this->t('There seems to be circular dependency in the flow.'));
        return;
      }
    }

    // Iterate over all the nodes in execution order and fill precedent nodes.
    foreach ($execution_order as $nid) {
      $current_precedent_nids = [];
      foreach ($precedent_nids[$nid] as $precedent_nid) {
        $current_precedent_nids = array_merge($current_precedent_nids, $precedent_nids[$precedent_nid]);
      }
      $precedent_nids[$nid] = array_unique(array_merge($precedent_nids[$nid], $current_precedent_nids));
    }

    // Iterate over all the nodes in reverse execution order and fill dependent
    // nodes.
    foreach (array_reverse($execution_order) as $nid) {
      $current_dependent_nids = [];
      foreach ($dependent_nids[$nid] as $dependent_nid) {
        $current_dependent_nids = array_merge($current_dependent_nids, $dependent_nids[$dependent_nid]);
      }
      $dependent_nids[$nid] = array_unique(array_merge($dependent_nids[$nid], $current_dependent_nids));
    }

    $processed_data = [
      'execution_order' => $execution_order,
      'precedent_nids' => $precedent_nids,
      'dependent_nids' => $dependent_nids,
    ];

    $form_state->setValue('processed_data', serialize($processed_data));
  }

  /**
   * Save function for saving ifthenelse rule entity.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Example.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Example was not saved.', [
        '%label' => $entity->label(),
      ]), MessengerInterface::TYPE_ERROR);
    }

    // Redirect to Rule list page.
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('ifthenelserule')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
