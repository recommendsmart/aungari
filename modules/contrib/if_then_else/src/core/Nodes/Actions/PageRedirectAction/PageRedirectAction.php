<?php

namespace Drupal\if_then_else\core\Nodes\Actions\PageRedirectAction;

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\if_then_else\core\Nodes\Actions\Action;
use Drupal\if_then_else\Event\NodeSubscriptionEvent;
use Drupal\if_then_else\Event\NodeValidationEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Page redirection action node class.
 */
class PageRedirectAction extends Action {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getName() {
    return 'page_redirect_action';
  }

  /**
   * {@inheritdoc}
   */
  public function registerNode(NodeSubscriptionEvent $event) {
    $event->nodes[static::getName()] = [
      'label' => $this->t('Page Redirect'),
      'description' => $this->t('Page Redirect'),
      'type' => 'action',
      'class' => 'Drupal\\if_then_else\\core\\Nodes\\Actions\\PageRedirectAction\\PageRedirectAction',
      'library' => 'if_then_else/PageRedirectAction',
      'control_class_name' => 'PageRedirectActionControl',
      'inputs' => [
        'url' => [
          'label' => $this->t('URL'),
          'description' => $this->t('URL, can be absolute or relative, internal or external.'),
          'sockets' => ['string.url', 'string'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateNode(NodeValidationEvent $event) {
    $data = $event->node->data;
    if (!property_exists($data, 'input_selection')) {
      $event->errors[] = $this->t('Provide an absolute or relative, internal or external URL to redirect to in "@node_name".', ['@node_name' => $event->node->name]);
      return;
    }

    $inputs = $event->node->inputs;
    if ($data->input_selection == 'value' && (!property_exists($data, 'value') || empty(trim($data->value)))) {
      $event->errors[] = $this->t('Provide an absolute or relative, internal or external URL to redirect to in "@node_name".', ['@node_name' => $event->node->name]);
    }
    elseif ($data->input_selection == 'input' && !count($inputs->url->connections)) {
      $event->errors[] = $this->t('Provide an absolute or relative, internal or external URL to redirect to in "@node_name".', ['@node_name' => $event->node->name]);
    }
  }

  /**
   * Process page redirection node.
   */
  public function process() {
    if ($this->data->input_selection == 'value') {
      $url = Html::escape($this->data->value);
    }
    else {
      $url = $this->inputs['url'];
    }
    // Checking slash if not exist then adding slash to alias.
    $url = rtrim(trim(trim($url), ''), "\\/");
    if ($url[0] !== '/') {
      $url = '/' . $url;
    }
    $path = URL::fromUserInput($url)->toString();
    $redirect = new RedirectResponse($path);
    $redirect->send();
  }

}
