<?php

namespace Drupal\if_then_else\Controller;

use Drupal\Core\Url;
use Drupal\if_then_else\Entity\IfthenelseRuleInterface;
use Drupal\if_then_else\Event\EventFilterEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use function GuzzleHttp\json_decode;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\EventConditionEvent;

/**
 * If Then Else Central Controller class.
 */
class IfThenElseController {
  use StringTranslationTrait;

  /**
   * Returns all rule config for a specific event.
   *
   * @param string $event_name
   *   Event name.
   * @param string $args
   *   Argument.
   *
   * @return array
   *   An array of applicable if then else rules.
   */
  public static function eventPresent($event_name, $args) {
    $events = [];

    // Added to fix an error message when uninsatlling this module.
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('if_then_else')) {
      $query = \Drupal::entityQuery('ifthenelserule')
        ->condition('active', TRUE)
        ->condition('event', $event_name)
        ->sort('weight', 'DESC');

      $event_filter_event = new EventFilterEvent($query, $args);
      $event_dispatcher = \Drupal::service('event_dispatcher');
      $event_dispatcher->dispatch('if_then_else_' . $event_name . '_event_filter_event', $event_filter_event);

      $enabled_rule_ids = $query->execute();

      $events = \Drupal::entityTypeManager()->getStorage('ifthenelserule')->loadMultiple($enabled_rule_ids);
    }

    return $events;
  }

  /**
   * Central Process function.
   */
  public static function process($event_name, array $input) {
    // Checking for an event in rules.
    $rules = static::eventPresent($event_name, $input);

    // Return if no rule has a form alter event.
    if (empty($rules)) {
      return;
    }
    // Get all the services before Nodes class called.
    $services = static::getAllServices($rules);

    $node_objects = [];
    $nids_to_be_skipped = [];
    foreach ($rules as $rule) {
      $data = json_decode($rule->data);
      $processed_data = unserialize($rule->processed_data);
      foreach ($processed_data['execution_order'] as $nid) {
        if (in_array($nid, $nids_to_be_skipped)) {
          continue;
        }

        $node = $data->nodes->{$nid};
        $class = $node->data->class;

        if (!class_exists($class)) {
          return;
        }

        // Pass arguments to the class if the object is set.
        // Since we are using dependency injecton on the Class services,
        // we do have pass the arguments to it.
        if (isset($node->data->classArg)) {
          $arguments = static::addServices($services, $node->data->classArg);
          $node_obj = new $class(...$arguments);
        }
        else {
          /* @var \Drupal\if_then_else\core\Nodes\Node $node_obj */
          $node_obj = new $class();
        }
        $node_obj->setData($node->data);
        if ($node->data->type == 'event') {
          $node_obj->setInputs($input);
        }
        elseif (count((array) $node->inputs)) {
          // Get outputs from precedent nodes.
          $inputs = [];
          foreach ($node->inputs as $socket_name => $output) {
            if (count($output->connections)) {
              foreach ($output->connections as $connection) {
                /* @var \Drupal\if_then_else\core\Nodes\Node $precedent_node_object */
                $precedent_node_object = $node_objects[$connection->node];
                $precedent_socket_name = $connection->output;
                $precedent_node_outputs = $precedent_node_object->getOutputs();
                $inputs[$socket_name] = &$precedent_node_outputs[$precedent_socket_name];
              }
            }
          }

          if (array_key_exists('execute', $inputs) && $inputs['execute'] === FALSE) {
            // Node shouldn't be executed.
            // Even its dependent nodes should not be executed.
            $nids_to_be_skipped = array_unique(array_merge($nids_to_be_skipped, $processed_data['dependent_nids'][$nid]));
            continue;
          }
          else {
            $node_obj->setInputs($inputs);
          }
        }

        $node_obj->process();

        if ($node->data->type == 'action' || $node->data->type == 'event') {
          $outputs = $node_obj->getOutputs();
          if ($outputs == NULL || !array_key_exists('success', $outputs)) {
            $node_obj->setSuccess(TRUE);
          }
        }
        $node_objects[$nid] = $node_obj;
      }

      // Check if debuggin is enabled for rules.
      $debugging = \Drupal::config('if_then_else.adminsettings')->get('enable_debugging');
      if ($debugging) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('Ifthenelse rule @rule triggered', ['@rule' => $rule->label]), $messenger::TYPE_STATUS);
      }
    }
  }

  /**
   * Disable route callback to disbale the IfThenElseRules.
   */
  public function disable(IfthenelseRuleInterface $ifthenelserule = NULL) {
    $ifthenelserule->setActive(FALSE);
    $ifthenelserule->save();
    \Drupal::messenger()->addMessage($this->t('%rule_label is disabled.', ['%rule_label' => $ifthenelserule->label()]));
    return new RedirectResponse($ifthenelserule->toUrl('collection')->toString());
  }

  /**
   * Enable route callback to enable the IfThenElseRules.
   */
  public function enable(IfthenelseRuleInterface $ifthenelserule = NULL) {
    // Instantiate our event.
    $event = new NodeSubscriptionEvent();

    // Define event dispatcher.
    $event_dispatcher = \Drupal::service('event_dispatcher');

    // Get the event_dispatcher server and dispatch the event.
    $event_dispatcher->dispatch(NodeSubscriptionEvent::EVENT_NAME, $event);
    $data = json_decode($ifthenelserule->data);
    foreach ($data->nodes as $node) {
      if (property_exists($node->data, 'dependencies') && !empty($node->data->dependencies)) {
        foreach ($node->data->dependencies as $module) {
          if (!\Drupal::moduleHandler()->moduleExists($module)) {
            \Drupal::messenger()->addMessage($this->t("@module_name module is not enabled. Rule @rule_name can't be enabled.", ['@module_name' => ucfirst($module), '@rule_name' => $ifthenelserule->label()]), 'warning');
            return new RedirectResponse($ifthenelserule->toUrl('collection')->toString());
          }
        }
      }
    }

    // Validation.
    $has_error = FALSE;
    // Make sure that the graph has only one event node.
    foreach ($data->nodes as $nid => $node) {
      if ($node->data->type == 'event') {
        if (empty($event_name)) {
          $event_name = $node->data->name;
        }
        else {
          // There are two events in this graph. Set an error.
          \Drupal::messenger()->addError($this->t('The graph has at least two event nodes. A graph should have exactly one event node.'));
          $has_error = TRUE;
        }
      }
    }
    if (empty($event_name)) {
      \Drupal::messenger()->addError($this->t('The graph has no event node. Add one event node to indicate when the graph should execute.'));
      $has_error = TRUE;
    }
    // Validate the full graph.
    $graph_errors = [];
    foreach ($data->nodes as $nid => $node) {
      $graph_validation_event = new GraphValidationEvent($data);
      $event_dispatcher->dispatch('if_then_else_' . $node->data->name . '_graph_validation_event', $graph_validation_event);
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
      \Drupal::messenger()->addError($content);
      $has_error = TRUE;
    }

    // Validate each node.
    $node_errors = [];
    foreach ($data->nodes as $nid => $node) {
      $node_validation_event = new NodeValidationEvent($nid, $node);
      $event_dispatcher->dispatch('if_then_else_' . $node->data->name . '_node_validation_event', $node_validation_event);
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
      \Drupal::messenger()->addError($content);
      $has_error = TRUE;
    }
    $precedent_nids = [];
    $dependent_nids = [];
    $execution_order = [];

    $event_condition = '';

    // Get execution order of precedent and dependent nodes and event condition.
    // Show an error if a required input is not connected.
    foreach ($data->nodes as $nid => $node) {
      $inputs = $node->inputs;
      $outputs = $node->outputs;

      if ($node->data->type == 'event') {
        $event_condition_event = new EventConditionEvent($node->data);
        $event_dispatcher->dispatch('if_then_else_' . $node->data->name . '_event_condition_event', $event_condition_event);
        $event_condition = implode(',', $event_condition_event->conditions);
      }
      $defined_inputs = array_key_exists('inputs', $event->nodes[$node->data->name]) ? $event->nodes[$node->data->name]['inputs'] : [];
      foreach ($defined_inputs as $input_name => $defined_input) {
        if (in_array('required', $defined_input) && $defined_input['required'] && !count($node->inputs->{$input_name}->connections)) {
          // A required input is not connected.
          \Drupal::messenger()->addError($this->t('Required input "@input_name" of "@node_name" is not connected.', ['@input_name' => $defined_input['label'], '@node_name' => $node->name]));
          $has_error = TRUE;
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
        \Drupal::messenger()->addError($this->t('There seems to be circular dependency in the flow.'));
        $has_error = TRUE;
      }
    }
    if (!$has_error) {
      $ifthenelserule->setActive(TRUE);

      // Iterate over all the nodes in execution order and fill precedent nodes.
      foreach ($execution_order as $nid) {
        $current_precedent_nids = [];
        foreach ($precedent_nids[$nid] as $precedent_nid) {
          $current_precedent_nids = array_merge($current_precedent_nids, $precedent_nids[$precedent_nid]);
        }
        $precedent_nids[$nid] = array_unique(array_merge($precedent_nids[$nid], $current_precedent_nids));
      }

      // Iterate over all the nodes in reverse execution order and fill
      // dependent nodes.
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
      $ifthenelserule->setValue('processed_data', serialize($processed_data));
      $ifthenelserule->save();

      \Drupal::messenger()->addMessage($this->t('%rule_label is enabled.', ['%rule_label' => $ifthenelserule->label()]));
    }
    return new RedirectResponse($ifthenelserule->toUrl('collection')->toString());
  }

  /**
   * Enable route callback to enable the IfThenElseRules.
   */
  public function entityClone(IfthenelseRuleInterface $ifthenelserule = NULL) {

    $clone_rule = $ifthenelserule->createDuplicate();
    $clone_rule->id = $ifthenelserule->id() . '_clone';
    $clone_rule->label = $ifthenelserule->label() . '_clone';
    $clone_rule->save();
    \Drupal::messenger()->addMessage($this->t('%rule_label is duplicated.', ['%rule_label' => $ifthenelserule->label()]));
    $path = Url::fromRoute(
        'entity.ifthenelserule.edit_form',
        ['ifthenelserule' => $clone_rule->id]
    )->toString();
    return new RedirectResponse($path);
  }

  /**
   * Set title for ifthenelse entity edit page.
   */
  public function getTitle(IfthenelseRuleInterface $ifthenelserule = NULL) {
    return $ifthenelserule->label();
  }

  /**
   * Static function to get all the services.
   *
   * @param mixed $rules
   *   Rete rules data.
   *
   * @return array
   *   All services.
   */
  public static function getAllServices($rules) {
    $_arr_arg = [];
    foreach ($rules as $rule) {
      $data = json_decode($rule->data);
      $processed_data = unserialize($rule->processed_data);
      foreach ($processed_data['execution_order'] as $nid) {
        $node = $data->nodes->{$nid};
        if (isset($node->data->classArg)) {
          $services = $node->data->classArg;
          foreach ($services as $service) {
            $_arr_arg[$service] = \Drupal::service($service);
          }
        }
      }
    }
    return $_arr_arg;
  }

  /**
   * Static function to add the services.
   *
   * @param mixed $services
   *   All Services data.
   * @param array $classArg
   *   Arguements service data.
   *
   * @return array
   *   Return services.
   */
  public static function addServices($services, array $classArg) {
    $_arr_arg = [];
    if (isset($classArg)) {
      foreach ($classArg as $service) {
        $_arr_arg[] = $services[$service];
      }
    }

    return $_arr_arg;
  }

}
