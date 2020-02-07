<?php

namespace Drupal\entity_reference_revisions\Normalizer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem;
use Drupal\hal\LinkManager\LinkManagerInterface;
use Drupal\hal\Normalizer\EntityReferenceItemNormalizer;
use Drupal\serialization\EntityResolver\EntityResolverInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Defines a class for normalizing EntityReferenceRevisionItems.
 */
class EntityReferenceRevisionItemNormalizer extends EntityReferenceItemNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = EntityReferenceRevisionsItem::class;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EntityReferenceRevisionItemNormalizer object.
   *
   * @param \Drupal\hal\LinkManager\LinkManagerInterface $link_manager
   *   The hypermedia link manager.
   * @param \Drupal\serialization\EntityResolver\EntityResolverInterface $entity_resolver
   *   The entity resolver.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(LinkManagerInterface $link_manager, EntityResolverInterface $entity_resolver, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($link_manager, $entity_resolver);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function constructValue($data, $context) {
    $value = parent::constructValue($data, $context);
    if ($value) {
      // If no target_revision_id is set, attempt to resolve one based on the
      // target_id value as set in the parent method.
      if (!isset($data['target_revision_id'])) {
        /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $field_item */
        $field_item = $context['target_instance'];
        $target_type = $field_item->getFieldDefinition()->getSetting('target_type');
        if (!empty($data['target_type']) && $target_type !== $data['target_type']) {
          throw new UnexpectedValueException(sprintf('The field "%s" property "target_type" must be set to "%s" or omitted.', $field_item->getFieldDefinition()->getName(), $target_type));
        }
        /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
        $storage = $this->entityTypeManager->getStorage($target_type);
        if ($entity = $storage->load($value['target_id'])) {
          $data['target_revision_id'] = $entity->getRevisionId();
        }
        else {
          // Unable to load entity by target_id.
          throw new InvalidArgumentException(sprintf('No "%s" entity found with target_id "%s" for field "%s".', $data['target_type'], $value['target_id'], $field_item->getName()));
        }
      }
      $value['target_revision_id'] = $data['target_revision_id'];
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = array()) {
    $data = parent::normalize($field_item, $format, $context);
    $field_name = $field_item->getParent()->getName();
    $entity = $field_item->getEntity();
    $field_uri = $this->linkManager->getRelationUri($entity->getEntityTypeId(), $entity->bundle(), $field_name, $context);
    $data['_embedded'][$field_uri][0]['target_revision_id'] = $field_item->target_revision_id;
    return $data;
  }

}
