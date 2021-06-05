<?php


namespace App\Validator;


use App\Security\Util\SecurityFunction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordStrongValidator extends ConstraintValidator
{

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PasswordStrong) {
            throw new UnexpectedTypeException($constraint, ConstraintValidator::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!SecurityFunction::isPasswordStrong($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}