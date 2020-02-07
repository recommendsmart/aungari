<?php

namespace Drupal\if_then_else_commerce\core;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class defined to have common functions for ifthenelse commerce processing.
 */
class IfthenelseCommerceUtilities extends DefaultPluginManager implements IfthenelseCommerceUtilitiesInterface {
  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * Get product type lists.
   */
  public function getAllProductTypes() {
    $product_types = $this->entityTypeManager->getStorage('commerce_product_type')->loadMultiple();

    static $product_type_lists = [];
    foreach ($product_types as $product_type) {
      $product_type_lists[] = ['id' => $product_type->id(), 'label' => $product_type->label()];
    }
    return $product_type_lists;
  }

  /**
   * Get product sku lists.
   */
  public function getAllProductSku() {
    $query = $this->entityTypeManager->getStorage('commerce_product_variation')
      ->getQuery()->condition('status', TRUE);
    $product_variation_ids = $query->execute();
    $product_types = $this->entityTypeManager->getStorage('commerce_product_variation')->loadMultiple($product_variation_ids);

    static $product_type_lists = [];
    foreach ($product_types as $product_type) {
      $product_type_lists[] = ['id' => $product_type->getProductId(), 'label' => $product_type->getSku()];
    }
    return $product_type_lists;
  }

}
