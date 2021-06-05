<?php

namespace App\Entity;

use App\Repository\CoOwnerRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CoOwnerRepository::class)
 */
class CoOwner
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id = null;

    /**
     * @ORM\Column(type="integer")
     */
    private $civility;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="coOwners")
     * @ORM\JoinColumn(nullable=false)
     */
    private $attachedOrder;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function __toString(): string
    {
        return Address::CIVILITY[$this->civility] . ' ' . $this->firstname . ' ' . $this->lastname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivility(): ?int
    {
        return $this->civility;
    }

    public function setCivility(int $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAttachedOrder(): ?Order
    {
        return $this->attachedOrder;
    }

    public function setAttachedOrder(?Order $attachedOrder): self
    {
        $this->attachedOrder = $attachedOrder;

        return $this;
    }
}
