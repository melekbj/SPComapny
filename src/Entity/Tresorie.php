<?php

namespace App\Entity;

use App\Repository\TresorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TresorieRepository::class)]
class Tresorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $solde_r = null;

    #[ORM\Column(type:"json",nullable: true)]
    private ?array $entree = null;

    #[ORM\Column(nullable: true)]
    private ?float $sortie = null;

    #[ORM\OneToMany(mappedBy: 'tresorie', targetEntity: Banques::class)]
    private Collection $banques;

    #[ORM\ManyToOne(inversedBy: 'tresories')]
    private ?Banques $banque = null;

    #[ORM\ManyToOne(inversedBy: 'tresories')]
    private ?Pays $pays = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descE = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descS = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviseE = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviseS = null;


    public function __construct()
    {
        $this->banques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoldeR(): ?float
    {
        return $this->solde_r;
    }

    public function setSoldeR(?float $solde_r): static
    {
        $this->solde_r = $solde_r;

        return $this;
    }

    // public function getEntree(): ?float
    // {
    //     return $this->entree;
    // }

    // public function setEntree(?float $entree): static
    // {
    //     $this->entree = $entree;

    //     return $this;
    // }
    public function getEntree(): ?array
    {
        return $this->entree;
    }

    public function setEntree(?array $entree): self
    {
        $this->entree = $entree;

        return $this;
    }

    public function getSortie(): ?float
    {
        return $this->sortie;
    }

    public function setSortie(?float $sortie): static
    {
        $this->sortie = $sortie;

        return $this;
    }

    public function getBanque(): ?Banques
    {
        return $this->banque;
    }

    public function setBanque(?Banques $banque): static
    {
        $this->banque = $banque;

        return $this;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescE(): ?string
    {
        return $this->descE;
    }

    public function setDescE(?string $descE): static
    {
        $this->descE = $descE;

        return $this;
    }

    public function getDescS(): ?string
    {
        return $this->descS;
    }

    public function setDescS(?string $descS): static
    {
        $this->descS = $descS;

        return $this;
    }

    public function getDeviseE(): ?string
    {
        return $this->deviseE;
    }

    public function setDeviseE(?string $deviseE): static
    {
        $this->deviseE = $deviseE;

        return $this;
    }

    public function getDeviseS(): ?string
    {
        return $this->deviseS;
    }

    public function setDeviseS(?string $deviseS): static
    {
        $this->deviseS = $deviseS;

        return $this;
    }



}
