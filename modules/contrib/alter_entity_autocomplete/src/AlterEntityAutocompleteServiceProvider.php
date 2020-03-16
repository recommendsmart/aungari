<?php

namespace Drupal\alter_entity_autocomplete;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 *
 */
class AlterEntityAutocompleteServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   *
   */
   public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('entity.autocomplete_matcher');
    $definition->setClass('Drupal\alter_entity_autocomplete\AlterEntityAutocomplete\AlterEntityAutocompleteMatcher');
  }
}
