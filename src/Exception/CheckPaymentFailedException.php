<?php


namespace App\Exception;


use Exception;

class CheckPaymentFailedException extends Exception
{
    public function errorMessage(): string
    {
        return 'Nous n\'avons pas pu vérifier votre paiement, nous nous excusons de la gêne occasionnée, si le problème persiste merci de nous contacter';
    }
}