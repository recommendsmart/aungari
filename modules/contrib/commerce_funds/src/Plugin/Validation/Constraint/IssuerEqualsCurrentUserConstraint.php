<?php

namespace Drupal\commerce_funds\Plugin\Validation\Constraint;

use Drupal\Core\Annotation\Translation;
use Symfony\Component\Validator\Constraint;

 /**
  * @Constraint(
  *  id = "IssuerEqualsCurrentUser",
  *  label = @Translation("Issuer equals current user.", context="Validation")
  * )
  */
 class IssuerEqualsCurrentUserConstraint extends Constraint {
   public $message = "Operation impossible. You can't transfer money to yourself.";
 }
