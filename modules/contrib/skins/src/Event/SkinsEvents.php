<?php

namespace Drupal\skins\Event;

/**
 * Defines events for the component schema system.
 */
final class SkinsEvents {

  /**
   * Name of the event fired when assembling skins.
   *
   * This event allows subscribers to add or modify skins. The event listener
   * method receives a \Drupal\skins\Event\SkinsTransformEvent instance. This
   * event contains a skins array which subscribers can interact with and which
   * will finally be used to provide theme skins.
   *
   * @code
   *   $skins = $event->getSkins();
   * @endcode
   *
   * @Event
   *
   * @var string
   */
  const TRANSFORM_BUILD = 'skins.transform.build';

}
