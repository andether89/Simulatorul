<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordMatch extends Constraint
{
    public $message = 'Your password doesn\'t match each other, please try again with both same passwords';
}