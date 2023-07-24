<?php

namespace App\Entity;

use App\Repository\TresorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(nullable: true)]
    private ?float $entree = null;

    #[ORM\Column(nullable: true)]
    private ?float $sortie = null;

    #[ORM\OneToMany(mappedBy: 'tresorie', targetEntity: Banques::class)]
    private Collection $banques;

    #[ORM\ManyToOne(inversedBy: 'tresories')]
    private ?Banques $banque = null;

    #[ORM\ManyToOne(inversedBy: 'tresories')]
    private ?Pays $pays = null;

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

    public function getEntree(): ?float
    {
        return $this->entree;
    }

    public function setEntree(?float $entree): static
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

}
