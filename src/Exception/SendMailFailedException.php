<?php


namespace App\Exception;


use Exception;

class SendMailFailedException extends Exception
{
    public function errorMessage(): string
    {
        return 'Une erreur est survenue lors de l\'envoi du mail, nous nous excusons pour ce problème technique et nous empressons de le résoudre';
    }
}