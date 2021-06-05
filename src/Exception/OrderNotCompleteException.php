<?php


namespace App\Exception;


use Exception;

class OrderNotCompleteException extends Exception
{
    public function errorMessage(): string
    {
        return 'The Order is not completed';
    }
}