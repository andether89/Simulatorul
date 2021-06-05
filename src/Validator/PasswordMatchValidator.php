<?php


namespace App\Validator;


use App\Security\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordMatchValidator extends ConstraintValidator
{

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PasswordMatch) {
            throw new UnexpectedTypeException($constraint, ConstraintValidator::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof UserInterface) {
            throw new UnexpectedValueException($value, UserInterface::class);
        }

        if ($value->getPlainPassword() !== $value->getPassword()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}