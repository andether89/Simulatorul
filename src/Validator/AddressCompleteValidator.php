<?php


namespace App\Validator;


use App\Entity\Address;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressCompleteValidator extends ConstraintValidator
{

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AddressComplete) {
            throw new UnexpectedTypeException($constraint, ConstraintValidator::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Address) {
            throw new UnexpectedValueException($value, Address::class);
        }

        if (
            ($value->getType() === 0 && (empty($value->getFirstname()) || empty($value->getLastname()) || $value->getCivility() === null)) ||
            ($value->getType() === 1 && (empty($value->getSiren()) || empty($value->getSocialReason())))
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}