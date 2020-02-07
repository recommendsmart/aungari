<?php

namespace Drupal\profile_form_mode\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\profile\Controller\ProfileController as BaseProfileController;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\profile\Entity\ProfileTypeInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses for ProfileController routes.
 */
class ProfileController extends BaseProfileController {

  /**
   * The form mode.
   *
   * @var string
   */
  protected $formMode;

  /**
   * Constructs a new ProfileController object.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(TimeInterface $time) {
    parent::__construct($time);
    $this->formMode = \Drupal::routeMatch()->getParameter('entity_form_mode');
  }

  /**
   * Provides the profile submission form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   *
   * @return array
   *   A profile submission form.
   */
  public function addProfile(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type) {
    try {
      $profile = $this->entityTypeManager()->getStorage('profile')->create([
        'uid' => $user->id(),
        'type' => $profile_type->id(),
      ]);

      return $this->entityFormBuilder()
        ->getForm($profile, $this->formMode, [
          'uid' => $user->id(),
          'created' => $this->time->getRequestTime(),
        ]);
    }
    catch (InvalidPluginDefinitionException $e) {
      $formModeOptions = \Drupal::service('entity_display.repository')
        ->getFormModeOptions('profile');
      if (isset($formModeOptions[$this->formMode])) {
        $this->messenger()
          ->addWarning("Site's cache must be cleared after enabling a the new form mode:" . $this->formMode . " on profile");
      }
      throw new NotFoundHttpException();
    }
  }

  /**
   * Provides the profile edit form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The profile entity to edit.
   *
   * @return array
   *   The profile edit form.
   */
  public function editProfile(RouteMatchInterface $route_match, UserInterface $user, ProfileInterface $profile) {
    try {
      return $this->entityFormBuilder()->getForm($profile, $this->formMode);
    }
    catch (InvalidPluginDefinitionException $e) {
      $formModeOptions = \Drupal::service('entity_display.repository')
        ->getFormModeOptions('profile');
      if (isset($formModeOptions[$this->formMode])) {
        $this->messenger()
          ->addWarning("Site's cache must be cleared after enabling a the new form mode:" . $this->formMode . " on profile");
      }
      throw new NotFoundHttpException();
    }
  }

}
