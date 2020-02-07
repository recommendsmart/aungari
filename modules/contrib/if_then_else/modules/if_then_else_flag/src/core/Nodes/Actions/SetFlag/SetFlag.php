<?php

namespace Drupal\if_then_else_flag\core\Nodes\Actions\SetFlag;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;
use Drupal\if_then_else\Event\GraphValidationEvent;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlagServiceInterface;

/**
 * Set flag class.
 */
class SetFlag extends Action {
  use StringTranslationTrait;

  /**
   * The ifthenelse utitlities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * The current user injected into the service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse utitlities.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\flag\FlagServiceInterface $flag_service
   *   The flag service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities,
                              EntityTypeManagerInterface $entity_manager,
                              FlagServiceInterface $flag_service,
                              AccountInterface $current_user) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
    $this->entityTypeManager = $entity_manager;
    $this->flagService = $flag_service;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'set_flag_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $form_entity_info = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    $flags = $this->entityTypeManager->getStorage('flag')->loadMultiple();
    $flag_list = [];
    foreach ($flags as $flag_id => $flag) {
      $flag_list[$flag_id]['name'] = $flag->label();
      $flag_list[$flag_id]['code'] = $flag_id;
      $flag_bundles = $flag->getBundles();
      $ent_id = $flag->getFlagTypePlugin()->getPluginId();
      $value = explode(':', $ent_id);
      $entity_id = $value[1];
      if (!empty($flag_bundles)) {
        foreach ($flag_bundles as $flag_bundle) {
          $flag_list[$flag_id]['flag'][] = [
            'code' => $flag_bundle,
            'name' => $form_entity_info[$entity_id]['bundles'][$flag_bundle]['label'],
            'entity_id' => $entity_id,
          ];
        }
      }
      if (empty($flag_bundles)) {
        foreach ($form_entity_info as $entity_bundle) {
          if ($entity_bundle['entity_id'] == $entity_id) {
            foreach ($entity_bundle['bundles'] as $bundle) {
              $flag_list[$flag_id]['flag'][] = [
                'code' => $bundle['bundle_id'],
                'name' => $bundle['label'],
                'entity_id' => $entity_bundle['entity_id'],
              ];
            }
          }
        }
      }
    }
    $flag_array = [];
    $i = 0;
    $flag_bundles = [];
    foreach ($flag_list as $flag_type) {
      $flag_array[$i]['name'] = $flag_type['name'];
      $flag_array[$i]['code'] = $flag_type['code'];
      $j = 0;
      if (isset($flag_type['flag'])) {
        foreach ($flag_type['flag'] as $flag) {
          $flag_bundles[$flag_type['code']][$j]['code'] = $flag['code'];
          $flag_bundles[$flag_type['code']][$j]['name'] = $flag['name'];
          $flag_bundles[$flag_type['code']][$j]['entity_id'] = $flag['entity_id'];
          $j++;
        }
      }
      $i++;
    }
    $event->nodes[static::getName()] = [
      'label' => $this->t('Set Flag'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else_flag\\core\\Nodes\\Actions\\SetFlag\\SetFlag',
      'classArg' => ['ifthenelse.utilities',
        'entity_type.manager',
        'flag',
        'current_user',
      ],
      'dependencies' => ['flag', 'if_then_else_flag'],
      'library' => 'if_then_else_flag/SetFlag',
      'control_class_name' => 'SetFlagActionControl',
      'component_class_name' => 'SetFlagActionComponent',
      'flag_types' => $flag_array,
      'flag_bundles' => $flag_bundles,
      'inputs' => [
        'entity' => [
          'label' => $this->t('Entity'),
          'description' => $this->t('Entity object.'),
          'sockets' => ['object.entity'],
          'required' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateGraph(GraphValidationEvent $event) {
    $nodes = $event->data->nodes;
    foreach ($nodes as $node) {
      if ($node->data->type == 'event' && $node->data->name == 'entity_load_event') {
        $event->errors[] = $this->t('Set Flag node will not work with  "@node_name".', ['@node_name' => $node->data->name]);
      }
    }
  }

  /**
   * Validation function.
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (empty($data->selected_options)) {
      $event->errors[] = $this->t('Selected a options name to fetch it\'s value in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $entity = $this->inputs['entity'];
    $flag_id = $this->data->selected_options->code;

    // Loading flag by flag id.
    $flag = $this->flagService->getFlagById($flag_id);
    // Check if already flagged.
    $flagging = $this->flagService->getFlagging($flag, $entity, $this->currentUser);
    if (!$flagging) {
      $this->flagService->flag($flag, $entity, $this->currentUser);
      $this->setSuccess(TRUE);
    }
  }

}
