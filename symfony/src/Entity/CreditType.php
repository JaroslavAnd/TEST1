<?php

namespace App\Entity;

use App\Repository\CreditTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditTypeRepository::class)]
#[ORM\Table(name: 'credit_types')]
class CreditType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $interestRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $minAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $maxAmount = null;

    #[ORM\Column]
    private ?int $termMonths = null;

    #[ORM\OneToMany(targetEntity: Credit::class, mappedBy: 'creditType')]
    private Collection $credits;

    public function __construct()
    {
        $this->credits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getInterestRate(): ?string
    {
        return $this->interestRate;
    }

    public function setInterestRate(string $interestRate): static
    {
        $this->interestRate = $interestRate;
        return $this;
    }

    public function getMinAmount(): ?string
    {
        return $this->minAmount;
    }

    public function setMinAmount(string $minAmount): static
    {
        $this->minAmount = $minAmount;
        return $this;
    }

    public function getMaxAmount(): ?string
    {
        return $this->maxAmount;
    }

    public function setMaxAmount(string $maxAmount): static
    {
        $this->maxAmount = $maxAmount;
        return $this;
    }

    public function getTermMonths(): ?int
    {
        return $this->termMonths;
    }

    public function setTermMonths(int $termMonths): static
    {
        $this->termMonths = $termMonths;
        return $this;
    }

    /**
     * @return Collection<int, Credit>
     */
    public function getCredits(): Collection
    {
        return $this->credits;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'interestRate' => (float)$this->interestRate,
            'minAmount' => (float)$this->minAmount,
            'maxAmount' => (float)$this->maxAmount,
            'termMonths' => $this->termMonths,
        ];
    }
}
