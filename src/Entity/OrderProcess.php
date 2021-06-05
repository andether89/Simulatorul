<?php

namespace App\Entity;

use App\Repository\OrderProcessRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderProcessRepository::class)
 */
class OrderProcess
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $processType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $purchasePlace;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $fourthChangeHome;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vehicleModification;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vehicleType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $circulationDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disability;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $demonstrationVehicle;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $administrativePower;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $collectionVehicle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $energy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $co2Rate;

    /**
     * @ORM\Column(type="integer")
     */
    private $department;

    /**
     * @ORM\Column(type="float")
     */
    private $y1TaxBeforeReduction;

    /**
     * @ORM\Column(type="float")
     */
    private $y1Tax;

    /**
     * @ORM\Column(type="float")
     */
    private $y2TransportVehicleSurcharge;

    /**
     * @ORM\Column(type="float")
     */
    private $y3Co2PenaltyPassengersCars;

    /**
     * @ORM\Column(type="float")
     */
    private $y4FixedTax;

    /**
     * @ORM\Column(type="float")
     */
    private $subtotal;

    /**
     * @ORM\Column(type="float")
     */
    private $y5RoutingFee;

    /**
     * @ORM\Column(type="float")
     */
    private $y6TaxesPayable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $fiveSeaterVan;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isCodeCarosseriePickUpBE;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPickUpAffectationRemonteesMecEtDomainesSki;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $vanPickUpSubmittedEcotax;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $vehicleN1CarryingTravellers;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $tourismVehicle;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $administrativePowerE85;

    /**
     * @ORM\Column(type="float")
     */
    private $totalToPay;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $communityReception;

    /**
     * @ORM\Column(type="integer")
     */
    private $process_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcessType(): ?string
    {
        return $this->processType;
    }

    public function setProcessType(string $processType): self
    {
        $this->processType = $processType;

        return $this;
    }

    public function getPurchasePlace(): ?string
    {
        return $this->purchasePlace;
    }

    public function setPurchasePlace(?string $purchasePlace): self
    {
        $this->purchasePlace = $purchasePlace;

        return $this;
    }

    public function getFourthChangeHome(): ?bool
    {
        return $this->fourthChangeHome;
    }

    public function setFourthChangeHome(?bool $fourthChangeHome): self
    {
        $this->fourthChangeHome = $fourthChangeHome;

        return $this;
    }

    public function getVehicleModification(): ?string
    {
        return $this->vehicleModification;
    }

    public function setVehicleModification(?string $vehicleModification): self
    {
        $this->vehicleModification = $vehicleModification;

        return $this;
    }

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?string $vehicleType): self
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getCirculationDate(): ?string
    {
        return $this->circulationDate;
    }

    public function setCirculationDate(string $circulationDate): self
    {
        $this->circulationDate = $circulationDate;

        return $this;
    }

    public function getDisability(): ?bool
    {
        return $this->disability;
    }

    public function setDisability(?bool $disability): self
    {
        $this->disability = $disability;

        return $this;
    }

    public function getRegistrationType(): ?string
    {
        return $this->registrationType;
    }

    public function setRegistrationType(?string $registrationType): self
    {
        $this->registrationType = $registrationType;

        return $this;
    }

    public function getDemonstrationVehicle(): ?bool
    {
        return $this->demonstrationVehicle;
    }

    public function setDemonstrationVehicle(?bool $demonstrationVehicle): self
    {
        $this->demonstrationVehicle = $demonstrationVehicle;

        return $this;
    }

    public function getAdministrativePower(): ?int
    {
        return $this->administrativePower;
    }

    public function setAdministrativePower(?int $administrativePower): self
    {
        $this->administrativePower = $administrativePower;

        return $this;
    }

    public function getCollectionVehicle(): ?bool
    {
        return $this->collectionVehicle;
    }

    public function setCollectionVehicle(?bool $collectionVehicle): self
    {
        $this->collectionVehicle = $collectionVehicle;

        return $this;
    }

    public function getEnergy(): ?string
    {
        return $this->energy;
    }

    public function setEnergy(?string $energy): self
    {
        $this->energy = $energy;

        return $this;
    }

    public function getCo2Rate(): ?int
    {
        return $this->co2Rate;
    }

    public function setCo2Rate(?int $co2Rate): self
    {
        $this->co2Rate = $co2Rate;

        return $this;
    }

    public function getDepartment(): ?int
    {
        return $this->department;
    }

    public function setDepartment(?int $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getY1TaxBeforeReduction(): ?float
    {
        return $this->y1TaxBeforeReduction;
    }

    public function setY1TaxBeforeReduction(float $y1TaxBeforeReduction): self
    {
        $this->y1TaxBeforeReduction = $y1TaxBeforeReduction;

        return $this;
    }

    public function getY1Tax(): ?float
    {
        return $this->y1Tax;
    }

    public function setY1Tax(float $y1Tax): self
    {
        $this->y1Tax = $y1Tax;

        return $this;
    }

    public function getY2TransportVehicleSurcharge(): ?float
    {
        return $this->y2TransportVehicleSurcharge;
    }

    public function setY2TransportVehicleSurcharge(float $y2TransportVehicleSurcharge): self
    {
        $this->y2TransportVehicleSurcharge = $y2TransportVehicleSurcharge;

        return $this;
    }

    public function getY3Co2PenaltyPassengersCars(): ?float
    {
        return $this->y3Co2PenaltyPassengersCars;
    }

    public function setY3Co2PenaltyPassengersCars(float $y3Co2PenaltyPassengersCars): self
    {
        $this->y3Co2PenaltyPassengersCars = $y3Co2PenaltyPassengersCars;

        return $this;
    }

    public function getY4FixedTax(): ?float
    {
        return $this->y4FixedTax;
    }

    public function setY4FixedTax(float $y4FixedTax): self
    {
        $this->y4FixedTax = $y4FixedTax;

        return $this;
    }

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getY5RoutingFee(): ?float
    {
        return $this->y5RoutingFee;
    }

    public function setY5RoutingFee(float $y5RoutingFee): self
    {
        $this->y5RoutingFee = $y5RoutingFee;

        return $this;
    }

    public function getY6TaxesPayable(): ?float
    {
        return $this->y6TaxesPayable;
    }

    public function setY6TaxesPayable(float $y6TaxesPayable): self
    {
        $this->y6TaxesPayable = $y6TaxesPayable;

        return $this;
    }

    public function getFiveSeaterVan(): ?bool
    {
        return $this->fiveSeaterVan;
    }

    public function setFiveSeaterVan(?bool $fiveSeaterVan): self
    {
        $this->fiveSeaterVan = $fiveSeaterVan;

        return $this;
    }

    public function getIsCodeCarosseriePickUpBE(): ?bool
    {
        return $this->isCodeCarosseriePickUpBE;
    }

    public function setIsCodeCarosseriePickUpBE(?bool $isCodeCarosseriePickUpBE): self
    {
        $this->isCodeCarosseriePickUpBE = $isCodeCarosseriePickUpBE;

        return $this;
    }

    public function getIsPickUpAffectationRemonteesMecEtDomainesSki(): ?bool
    {
        return $this->isPickUpAffectationRemonteesMecEtDomainesSki;
    }

    public function setIsPickUpAffectationRemonteesMecEtDomainesSki(?bool $isPickUpAffectationRemonteesMecEtDomainesSki): self
    {
        $this->isPickUpAffectationRemonteesMecEtDomainesSki = $isPickUpAffectationRemonteesMecEtDomainesSki;

        return $this;
    }

    public function getVanPickUpSubmittedEcotax(): ?bool
    {
        return $this->vanPickUpSubmittedEcotax;
    }

    public function setVanPickUpSubmittedEcotax(?bool $vanPickUpSubmittedEcotax): self
    {
        $this->vanPickUpSubmittedEcotax = $vanPickUpSubmittedEcotax;

        return $this;
    }

    public function getVehicleN1CarryingTravellers(): ?bool
    {
        return $this->vehicleN1CarryingTravellers;
    }

    public function setVehicleN1CarryingTravellers(?bool $vehicleN1CarryingTravellers): self
    {
        $this->vehicleN1CarryingTravellers = $vehicleN1CarryingTravellers;

        return $this;
    }

    public function getTourismVehicle(): ?bool
    {
        return $this->tourismVehicle;
    }

    public function setTourismVehicle(?bool $tourismVehicle): self
    {
        $this->tourismVehicle = $tourismVehicle;

        return $this;
    }

    public function getAdministrativePowerE85(): ?int
    {
        return $this->administrativePowerE85;
    }

    public function setAdministrativePowerE85(?int $administrativePowerE85): self
    {
        $this->administrativePowerE85 = $administrativePowerE85;

        return $this;
    }

    public function getTotalToPay(): ?float
    {
        return $this->totalToPay;
    }

    public function setTotalToPay(float $totalToPay): self
    {
        $this->totalToPay = $totalToPay;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCommunityReception(): ?bool
    {
        return $this->communityReception;
    }

    public function setCommunityReception(?bool $communityReception): self
    {
        $this->communityReception = $communityReception;

        return $this;
    }

    public function getProcessId(): ?int
    {
        return $this->process_id;
    }

    public function setProcessId(int $process_id): self
    {
        $this->process_id = $process_id;

        return $this;
    }
}
