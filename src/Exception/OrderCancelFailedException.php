<?php


namespace App\Exception;


use Exception;

class OrderCancelFailedException extends Exception
{
    public function errorMessage(): string
    {
        return 'Une erreur est survenue lors de votre tentative d\'annulation de votre commande, veuillez nous excuser pour la gêne occasionnée, si le problème persiste veuillez nous contacter';
    }
}