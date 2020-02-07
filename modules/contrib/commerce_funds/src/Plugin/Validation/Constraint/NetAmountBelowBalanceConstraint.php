<?php

namespace Drupal\commerce_funds\Plugin\Validation\Constraint;

use Drupal\Core\Annotation\Translation;
use Symfony\Component\Validator\Constraint;

 /**
  * @Constraint(
  *  id = "NetAmountBelowBalance",
  *  label = @Translation("Net amount is superior to balance amount.", context="Validation")
  * )
  */
 class NetAmountBelowBalanceConstraint extends Constraint {
   public $message = "You don't have enough funds to cover this transfer.";
   public $message_with_fee = "You don't have enough funds to cover this transfer.<br>
   The commission applied is %commission (@currency).";
 }
