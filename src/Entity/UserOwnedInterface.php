<?php


namespace App\Entity;

interface UserOwnedInterface
{
    public function setUser(?User $user);

    public function getUser(): ?User;
}