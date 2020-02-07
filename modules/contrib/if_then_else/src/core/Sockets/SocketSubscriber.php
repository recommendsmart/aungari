<?php

namespace Drupal\if_then_else\core\Sockets;

use Drupal\if_then_else\Event\SocketSubscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\if_then_else\core\IfthenelseUtilitiesInterface;

/**
 * Class SocketSubscriber.
 *
 * @package Drupal\if_then_else\core\Sockets
 */
class SocketSubscriber implements EventSubscriberInterface {

  /**
   * The ifthenelse Utilities.
   *
   * @var \Drupal\if_then_else\core\IfthenelseUtilitiesInterface
   */
  protected $ifthenelseUtilities;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\if_then_else\core\IfthenelseUtilitiesInterface $ifthenelse_utilities
   *   The ifthenelse Utilities.
   */
  public function __construct(IfthenelseUtilitiesInterface $ifthenelse_utilities) {
    $this->ifthenelseUtilities = $ifthenelse_utilities;
  }

  /**
   * Returns a list of events that this class subscribes to.
   */
  public static function getSubscribedEvents() {
    return [
      SocketSubscriptionEvent::EVENT_NAME => 'registerSocket',
    ];
  }

  /**
   * Register different socket types.
   *
   * @param \Drupal\if_then_else\Event\SocketSubscriptionEvent $event
   *   Event.
   */
  public function registerSocket(SocketSubscriptionEvent $event) {
    $event->sockets['form'] = 'Form Object';
    $event->sockets['form_state'] = 'Form State Object';
    $event->sockets['string'] = 'String';
    $event->sockets['string.url'] = 'URL';
    $event->sockets['bool'] = 'Boolean';
    $event->sockets['number'] = 'Number';
    $event->sockets['array'] = 'Array';
    $event->sockets['object.view'] = 'ViewExecutable';
    $event->sockets['object.field'] = 'Field';
    $event->sockets['object.field.text_with_summary'] = 'Field Text With Summary';
    $event->sockets['object.field.image'] = 'Field Image';
    $event->sockets['object.field.link'] = 'Field Link';
    $event->sockets['object.field.text_long'] = 'Field Text or Text Long';

    $event->sockets['object.entity'] = 'Entity';
    $entity_infos = $this->ifthenelseUtilities->getContentEntitiesAndBundles();
    if (!empty($entity_infos)) {
      foreach ($entity_infos as $entity_info) {
        $event->sockets['object.entity.' . $entity_info['entity_id']] = $entity_info['label'];
        if (!empty($entity_info['bundles'])) {
          foreach ($entity_info['bundles'] as $bundle) {
            $event->sockets['object.entity.' . $entity_info['entity_id'] . '.' . $bundle['bundle_id']] = $bundle['label'];
          }
        }
      }

      // Sockets for field types.
      $event->sockets['array.string'] = 'Array Strings';
      $event->sockets['array.string.url'] = 'Array URLs';
      $event->sockets['array.bool'] = 'Array Booleans';
      $event->sockets['array.number'] = 'Array Numbers';
      $event->sockets['array.object.field.text_with_summary'] = 'Array Field Text With Summaries';
      $event->sockets['array.object.field.image'] = 'Array Field Text With Summaries';
      $event->sockets['array.object.field.link'] = 'Array Field Text With Summaries';
      $event->sockets['array.object.field.text_long'] = 'Array Field Text With Summaries';
    }
  }

}
