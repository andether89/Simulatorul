<?php


namespace App\Entity;


interface StripeOrderInterface
{
    public const STATE = [
        0 => 'En attente de paiement',
        1 => 'En attente de réception des pièces',
        2 => 'En attente de validation des pièces',
        3 => 'En cours de traitement',
        4 => 'Terminée',
        5 => 'Annulée',
        6 => 'Annulée et remboursée',
    ];

    public function getPaymentIntent(): ?string;

    public function setPaymentIntent(string $paymentIntent);

    public function getStripeSession(): ?string;

    public function setStripeSession(?string $stripeSession);

    public function getState(): ?int;

    public function setState(int $state);

    public function getNumber(): ?string;

    public function setNumber(string $number);

    public function getTotal(): ?float;

    public function setTotal(float $total);

    public function setUser(?User $user);

    public function getUser(): ?User;
}