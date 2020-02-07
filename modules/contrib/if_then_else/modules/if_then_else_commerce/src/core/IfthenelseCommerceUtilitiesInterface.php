<?php

namespace Drupal\if_then_else_commerce\core;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Class defined to have common functions for ifthenelse commerce processing.
 */
interface IfthenelseCommerceUtilitiesInterface extends PluginManagerInterface, CachedDiscoveryInterface {

  /**
   * Get product sku lists.
   *
   * @return \Drupal\if_then_else\core\IfthenelseCommerceUtilitiesInterface[]
   *   Return array of product sku
   */
  public function getAllProductSku();

  /**
   * Get product type lists.
   *
   * @return \Drupal\if_then_else\core\IfthenelseCommerceUtilitiesInterface[]
   *   Return array of product type
   */
  public function getAllProductTypes();

}
