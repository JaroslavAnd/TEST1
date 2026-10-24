<?php

namespace App\Entity;

use App\Repository\CreditRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditRepository::class)]
#[ORM\Table(name: 'credits')]
class Credit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'credits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Client $client = null;

    #[ORM\ManyToOne(targetEntity: BankAccount::class, inversedBy: 'credits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?BankAccount $bankAccount = null;

    #[ORM\ManyToOne(targetEntity: CreditType::class, inversedBy: 'credits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?CreditType $creditType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $remainingAmount = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'active';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'credit', cascade: ['remove'], orphanRemoval: true)]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->startDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): static
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }

    public function getCreditType(): ?CreditType
    {
        return $this->creditType;
    }

    public function setCreditType(?CreditType $creditType): static
    {
        $this->creditType = $creditType;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getRemainingAmount(): ?string
    {
        return $this->remainingAmount;
    }

    public function setRemainingAmount(string $remainingAmount): static
    {
        $this->remainingAmount = $remainingAmount;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'clientId' => $this->client?->getId(),
            'clientName' => $this->client ? ($this->client->getFirstName() . ' ' . $this->client->getLastName()) : null,
            'bankAccountId' => $this->bankAccount?->getId(),
            'accountNumber' => $this->bankAccount?->getAccountNumber(),
            'creditTypeId' => $this->creditType?->getId(),
            'creditTypeName' => $this->creditType?->getName(),
            'amount' => (float)$this->amount,
            'remainingAmount' => (float)$this->remainingAmount,
            'startDate' => $this->startDate?->format('Y-m-d'),
            'endDate' => $this->endDate?->format('Y-m-d'),
            'status' => $this->status,
            'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
