<?php

namespace Drupal\if_then_else\EventSubscriber;

use Drupal\if_then_else\Controller\IfThenElseController;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Ifthenelse subscriber class.
 */
class IfthenelseSubscriber implements EventSubscriberInterface {

  /**
   * Register handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function registerIfThenElseEvent(GetResponseEvent $event) {
    $response = $event->getRequest();
    IfThenElseController::process('init_event', ['url' => $response->getPathInfo()]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['registerIfThenElseEvent'];
    return $events;
  }

}
