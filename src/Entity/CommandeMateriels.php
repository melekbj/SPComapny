<?php

namespace App\Entity;

use App\Repository\CommandeMaterielsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeMaterielsRepository::class)]
class CommandeMateriels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandeMateriels')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'commandeMateriels')]
    private ?Materiels $materiel = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixV = null;

    #[ORM\Column(nullable: true)]
    private ?float $remise = null;

    // Constructor
    public function __construct()
    {
        // Set the default value for prixV as pu from Materiels class
        if ($this->materiel !== null) {
            $this->prixV = $this->materiel->getPu();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getMateriel(): ?Materiels
    {
        return $this->materiel;
    }

    public function setMateriel(?Materiels $materiel): static
    {
        $this->materiel = $materiel;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrixV(): ?float
    {
        return $this->prixV;
    }

    public function setPrixV(float $prixV): static
    {
        $this->prixV = $prixV;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(?float $remise): static
    {
        $this->remise = $remise;

        return $this;
    }
}
