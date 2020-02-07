<?php

namespace Drupal\commerce_vendor\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Branch entities.
 */
class BranchViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
