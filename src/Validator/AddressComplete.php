<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AddressComplete extends Constraint
{
    public $message = 'Si vous êtes un particulier, les champs "Prénom", "Nom" et "Civilité" doivent être remplis, si vous êtes une entreprise merci de remplir les champs "raison sociale" et "SIRET/SIREN"';
}