<?php

namespace Drupal\skins\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SkinsTransformEvent.
 *
 * This event allows subscribers to alter the skins.
 */
class SkinsTransformEvent extends Event {

  /**
   * The skins to transform.
   *
   * @var array
   */
  protected $skins;

  /**
   * SkinsTransformEvent constructor.
   *
   * @param array
   *   The skins to transform.
   */
  public function __construct(array $skins) {
    $this->skins = $skins;
  }

  /**
   * Returns the skins.
   *
   * @return array
   *   The skins.
   */
  public function getSkins() {
    return $this->skins;
  }

}
