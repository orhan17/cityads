<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $externalId;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 32)]
    private ?string $currencyName = null;

    #[ORM\Column]
    private ?int $approvalTime = null;

    #[ORM\Column]
    private ?int $paymentTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(nullable: true)]
    private ?float $rating = null;

    /**
     * @var Collection<int, Geo>
     */
    #[ORM\ManyToMany(targetEntity: Geo::class, inversedBy: 'offers')]
    private Collection $geos;

    public function __construct()
    {
        $this->geos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
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

    public function getCurrencyName(): ?string
    {
        return $this->currencyName;
    }

    public function setCurrencyName(string $currencyName): static
    {
        $this->currencyName = $currencyName;

        return $this;
    }

    public function getApprovalTime(): ?int
    {
        return $this->approvalTime;
    }

    public function setApprovalTime(int $approvalTime): static
    {
        $this->approvalTime = $approvalTime;

        return $this;
    }

    public function getPaymentTime(): ?int
    {
        return $this->paymentTime;
    }

    public function setPaymentTime(int $paymentTime): static
    {
        $this->paymentTime = $paymentTime;

        return $this;
    }

    public function getSiteUrl(): ?string
    {
        return $this->siteUrl;
    }

    public function setSiteUrl(?string $siteUrl): static
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return Collection<int, Geo>
     */
    public function getGeos(): Collection
    {
        return $this->geos;
    }

    public function addGeo(Geo $geo): static
    {
        if (!$this->geos->contains($geo)) {
            $this->geos->add($geo);
        }

        return $this;
    }

    public function removeGeo(Geo $geo): static
    {
        $this->geos->removeElement($geo);

        return $this;
    }
}
