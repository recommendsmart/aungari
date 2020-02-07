<?php

namespace Drupal\if_then_else;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of ifthenelse rules.
 *
 * @see \Drupal\if_then_else\Entity\IfthenelseRule
 */
class IfthenelseRuleListBuilder extends DraggableListBuilder {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * RoleListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(EntityTypeInterface $entityType,
                              EntityStorageInterface $storage,
                              MessengerInterface $messenger) {
    parent::__construct($entityType, $storage);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ifthenelse_rules_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['edit'] = [
      'title' => $this
        ->t('Edit'),
      'weight' => 10,
      'url' => $entity
        ->toUrl('edit-form'),
    ];

    $operations['delete'] = [
      'title' => $this
        ->t('Delete'),
      'weight' => 100,
      'url' => $entity
        ->toUrl('delete-form'),
    ];

    $operations['clone'] = [
      'title' => $this
        ->t('Clone'),
      'weight' => 100,
      'url' => $entity->toUrl('clone'),
    ];

    if (!$entity->active) {
      $operations['enable'] = [
        'title' => $this
          ->t('Enable'),
        'weight' => 100,
        'url' => $entity->toUrl('enable'),
      ];
    }
    else {
      $operations['disable'] = [
        'title' => $this
          ->t('Disable'),
        'weight' => 100,
        'url' => $entity->toUrl('disable'),
      ];
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Create header of list.
    $header['label'] = $this->t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    // Create body of list.
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['enabled']['heading']['#markup'] = '<h2>' . $this->t('Enabled', [], ['context' => 'Plural']) . '</h2>';
    $form['enabled']['#weight'] = -3;
    $form['enabled'][$this->entitiesKey] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#empty' => $this->t('There are no @label enabled yet.', ['@label' => $this->entityType->getPluralLabel()]),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'weight',
        ],
      ],
      '#weight' => 1,
    ];

    $form['disabled']['heading']['#markup'] = '<h2>' . $this->t('Disabled', [], ['context' => 'Plural']) . '</h2>';
    $disabled_header = $this->buildHeader();
    unset($disabled_header['weight']);
    $form['disabled'][$this->entitiesKey] = [
      '#type' => 'table',
      '#header' => $disabled_header,
      '#empty' => $this->t('There are no @label disabled.', ['@label' => $this->entityType->getPluralLabel()]),
      '#weight' => 3,
    ];

    $this->entities = $this->load();
    $delta = 10;
    // Change the delta of the weight field if have more than 20 entities.
    if (!empty($this->weightKey)) {
      $count = count($this->entities);
      if ($count > 20) {
        $delta = ceil($count / 2);
      }
    }
    $button_status = FALSE;
    foreach ($this->entities as $entity) {
      $row = $this->buildRow($entity);
      if (isset($row['label'])) {
        $row['label'] = ['#markup' => $row['label']];
      }
      if (isset($row['weight'])) {
        $row['weight']['#delta'] = $delta;
      }
      if ($entity->active) {
        $form['enabled'][$this->entitiesKey][$entity->id()] = $row;
        $button_status = TRUE;
      }
      else {
        unset($row['weight']);
        $form['disabled'][$this->entitiesKey][$entity->id()] = $row;
      }
    }
    if ($button_status) {
      $form['actions']['#type'] = 'actions';
      $form['actions']['#weight'] = -2;
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
      ];

      $form['actions']['disable_rule'] = [
        '#title' => $this
          ->t('Disable All If Then Else'),
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.ifthenelse.disable_all'),
        '#attributes' => ['class' => 'button js-form-submit form-submit'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->messenger->addStatus($this->t('Ifthenelse rule setting have been updated.'));
  }

}
