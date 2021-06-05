<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @Vich\Uploadable
 */
class Order implements UserOwnedInterface, StripeOrderInterface
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
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=CoOwner::class, mappedBy="attachedOrder", orphanRemoval=true, cascade={"remove", "persist"})
     */
    private $coOwners;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $shippingAddress;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $billingAddress;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $total;

    /**
     * @ORM\Column(type="integer")
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentIntent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeSession;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numberPlate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $priority;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="registration_document", fileNameProperty="registrationDocumentName", originalName="registrationDocumentOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $registrationDocumentFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationDocumentName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="assignment_certificate", fileNameProperty="assignmentCertificateName", originalName="assignmentCertificateOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $assignmentCertificateFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $assignmentCertificateName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="registration_certificate", fileNameProperty="registrationCertificateName", originalName="registrationCertificateOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $registrationCertificateFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationCertificateName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="registration_mandate", fileNameProperty="registrationMandateName", originalName="registrationMandateOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $registrationMandateFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationMandateName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="driver_licence", fileNameProperty="driverLicenceName", originalName="driverLicenceOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $driverLicenceFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $driverLicenceName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="address_proof", fileNameProperty="addressProofName", originalName="addressProofOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $addressProofFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $addressProofName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="vehicle_insurance", fileNameProperty="vehicleInsuranceName", originalName="vehicleInsuranceOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $vehicleInsuranceFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vehicleInsuranceName;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="technical_control", fileNameProperty="technicalControlName", originalName="technicalControlOriginalName")
     * @Assert\File(
     *     mimeTypes={"image/jpeg", "application/pdf"},
     *     mimeTypesMessage="Nous ne prenons que les fichiers '.pdf' et '.jpg'",
     *     maxSize="2M",
     *     maxSizeMessage="La taille du fichier ne doit pas dépasser 2Mo"
     * )
     */
    private $technicalControlFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $technicalControlName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sendSms;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationDocumentOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $assignmentCertificateOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationCertificateOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrationMandateOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $driverLicenceOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $addressProofOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vehicleInsuranceOriginalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $technicalControlOriginalName;

    /**
     * @ORM\OneToOne(targetEntity=OrderProcess::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $process;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->coOwners = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->number;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

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

    /**
     * @return Collection|CoOwner[]
     */
    public function getCoOwners(): Collection
    {
        return $this->coOwners;
    }

    public function addCoOwner(CoOwner $coOwner): self
    {
        if (!$this->coOwners->contains($coOwner)) {
            $this->coOwners[] = $coOwner;
            $coOwner->setAttachedOrder($this);
        }

        return $this;
    }

    public function removeCoOwner(CoOwner $coOwner): self
    {
        if ($this->coOwners->removeElement($coOwner)) {
            // set the owning side to null (unless already changed)
            if ($coOwner->getAttachedOrder() === $this) {
                $coOwner->setAttachedOrder(null);
            }
        }

        return $this;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?Address $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPaymentIntent(): ?string
    {
        return $this->paymentIntent;
    }

    public function setPaymentIntent(string $paymentIntent): self
    {
        $this->paymentIntent = $paymentIntent;

        return $this;
    }

    public function getStripeSession(): ?string
    {
        return $this->stripeSession;
    }

    public function setStripeSession(?string $stripeSession): self
    {
        $this->stripeSession = $stripeSession;

        return $this;
    }

    public function getNumberPlate(): ?string
    {
        return $this->numberPlate;
    }

    public function setNumberPlate(?string $numberPlate): self
    {
        $this->numberPlate = $numberPlate;

        return $this;
    }

    public function getPriority(): ?bool
    {
        return $this->priority;
    }

    public function setPriority(bool $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @param File|null $registrationDocumentFile
     * @return $this
     */
    public function setRegistrationDocumentFile(?File $registrationDocumentFile = null): self
    {
        $this->registrationDocumentFile = $registrationDocumentFile;
        if (null !== $registrationDocumentFile) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getRegistrationDocumentFile(): ?File
    {
        return $this->registrationDocumentFile;
    }

    public function getRegistrationDocumentName(): ?string
    {
        return $this->registrationDocumentName;
    }

    public function setRegistrationDocumentName(?string $registrationDocumentName): self
    {
        $this->registrationDocumentName = $registrationDocumentName;

        return $this;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setAssignmentCertificateFile(?File $file = null): self
    {
        $this->assignmentCertificateFile = $file;
        if (null !== $file) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getAssignmentCertificateFile(): ?File
    {
        return $this->assignmentCertificateFile;
    }

    public function getAssignmentCertificateName(): ?string
    {
        return $this->assignmentCertificateName;
    }

    public function setAssignmentCertificateName(?string $assignmentCertificateName): self
    {
        $this->assignmentCertificateName = $assignmentCertificateName;

        return $this;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setRegistrationCertificateFile(?File $file = null): self
    {
        $this->registrationCertificateFile = $file;
        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getRegistrationCertificateFile(): ?File
    {
        return $this->registrationCertificateFile;
    }

    public function getRegistrationCertificateName(): ?string
    {
        return $this->registrationCertificateName;
    }

    public function setRegistrationCertificateName(?string $registrationCertificateName): self
    {
        $this->registrationCertificateName = $registrationCertificateName;

        return $this;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setRegistrationMandateFile(?File $file = null): self
    {
        $this->registrationMandateFile = $file;
        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    /**
     * @return File|null
     */
    public function getRegistrationMandateFile(): ?File
    {
        return $this->registrationMandateFile;
    }

    public function getRegistrationMandateName(): ?string
    {
        return $this->registrationMandateName;
    }

    public function setRegistrationMandateName(?string $registrationMandateName): self
    {
        $this->registrationMandateName = $registrationMandateName;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getDriverLicenceFile(): ?File
    {
        return $this->driverLicenceFile;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setDriverLicenceFile(?File $file): self
    {
        $this->driverLicenceFile = $file;
        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getDriverLicenceName(): ?string
    {
        return $this->driverLicenceName;
    }

    public function setDriverLicenceName(?string $driverLicenceName): self
    {
        $this->driverLicenceName = $driverLicenceName;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getAddressProofFile(): ?File
    {
        return $this->addressProofFile;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setAddressProofFile(?File $file): self
    {
        $this->addressProofFile = $file;
        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getAddressProofName(): ?string
    {
        return $this->addressProofName;
    }

    public function setAddressProofName(?string $addressProofName): self
    {
        $this->addressProofName = $addressProofName;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getVehicleInsuranceFile(): ?File
    {
        return $this->vehicleInsuranceFile;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setVehicleInsuranceFile(?File $file): self
    {
        $this->vehicleInsuranceFile = $file;
        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getVehicleInsuranceName(): ?string
    {
        return $this->vehicleInsuranceName;
    }

    public function setVehicleInsuranceName(?string $vehicleInsuranceName): self
    {
        $this->vehicleInsuranceName = $vehicleInsuranceName;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getTechnicalControlFile(): ?File
    {
        return $this->technicalControlFile;
    }

    /**
     * @param File|null $file
     * @return $this
     */
    public function setTechnicalControlFile(?File $file): self
    {
        $this->technicalControlFile = $file;
        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
        return $this;
    }

    public function getTechnicalControlName(): ?string
    {
        return $this->technicalControlName;
    }

    public function setTechnicalControlName(?string $technicalControlName): self
    {
        $this->technicalControlName = $technicalControlName;

        return $this;
    }

    public function getSendSms(): ?bool
    {
        return $this->sendSms;
    }

    public function setSendSms(bool $sendSms): self
    {
        $this->sendSms = $sendSms;

        return $this;
    }

    public function getRegistrationDocumentOriginalName(): ?string
    {
        return $this->registrationDocumentOriginalName;
    }

    public function setRegistrationDocumentOriginalName(?string $registrationDocumentOriginalName): self
    {
        $this->registrationDocumentOriginalName = $registrationDocumentOriginalName;

        return $this;
    }

    public function getAssignmentCertificateOriginalName(): ?string
    {
        return $this->assignmentCertificateOriginalName;
    }

    public function setAssignmentCertificateOriginalName(?string $assignmentCertificateOriginalName): self
    {
        $this->assignmentCertificateOriginalName = $assignmentCertificateOriginalName;

        return $this;
    }

    public function getRegistrationCertificateOriginalName(): ?string
    {
        return $this->registrationCertificateOriginalName;
    }

    public function setRegistrationCertificateOriginalName(?string $registrationCertificateOriginalName): self
    {
        $this->registrationCertificateOriginalName = $registrationCertificateOriginalName;

        return $this;
    }

    public function getRegistrationMandateOriginalName(): ?string
    {
        return $this->registrationMandateOriginalName;
    }

    public function setRegistrationMandateOriginalName(?string $registrationMandateOriginalName): self
    {
        $this->registrationMandateOriginalName = $registrationMandateOriginalName;

        return $this;
    }

    public function getDriverLicenceOriginalName(): ?string
    {
        return $this->driverLicenceOriginalName;
    }

    public function setDriverLicenceOriginalName(?string $driverLicenceOriginalName): self
    {
        $this->driverLicenceOriginalName = $driverLicenceOriginalName;

        return $this;
    }

    public function getAddressProofOriginalName(): ?string
    {
        return $this->addressProofOriginalName;
    }

    public function setAddressProofOriginalName(?string $addressProofOriginalName): self
    {
        $this->addressProofOriginalName = $addressProofOriginalName;

        return $this;
    }

    public function getVehicleInsuranceOriginalName(): ?string
    {
        return $this->vehicleInsuranceOriginalName;
    }

    public function setVehicleInsuranceOriginalName(?string $vehicleInsuranceOriginalName): self
    {
        $this->vehicleInsuranceOriginalName = $vehicleInsuranceOriginalName;

        return $this;
    }

    public function getTechnicalControlOriginalName(): ?string
    {
        return $this->technicalControlOriginalName;
    }

    public function setTechnicalControlOriginalName(?string $technicalControlOriginalName): self
    {
        $this->technicalControlOriginalName = $technicalControlOriginalName;

        return $this;
    }

    public function getProcess(): ?OrderProcess
    {
        return $this->process;
    }

    public function setProcess(OrderProcess $process): self
    {
        $this->process = $process;

        return $this;
    }
}
