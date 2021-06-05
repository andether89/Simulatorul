<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('_call', [$this, 'findMethod'])
        ];
    }

    /**
     * Allows you to find a method for a class with a string in Twig
     *
     * @param $class
     * @param string $function
     * @param array|null $arguments
     * @return false|mixed
     */
    public function findMethod($class, string $function, array $arguments = null)
    {
        return call_user_func([
            $class,
            $function
        ], $arguments);
    }

}