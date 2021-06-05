<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordStrong extends Constraint
{
    public $message = 'Your password isn\'t strong enough, you need to have at least one uppercase, one lowercase, 8 characters minimum and one special char';
}