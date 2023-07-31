<?php

namespace App\Entity;

use App\Repository\TresorieHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TresorieHistoryRepository::class)]
class TresorieHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $solde_r = null;

    #[ORM\Column]
    private ?float $entree = null;

    #[ORM\Column]
    private ?float $sortie = null;

    #[ORM\ManyToOne(inversedBy: 'tresorieHistories')]
    private ?Banques $banque = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoldeR(): ?float
    {
        return $this->solde_r;
    }

    public function setSoldeR(float $solde_r): static
    {
        $this->solde_r = $solde_r;

        return $this;
    }

    public function getEntree(): ?float
    {
        return $this->entree;
    }

    public function setEntree(float $entree): static
    {
        $this->entree = $entree;

        return $this;
    }

    public function getSortie(): ?float
    {
        return $this->sortie;
    }

    public function setSortie(float $sortie): static
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt = null): static
    {
        if ($updatedAt === null) {
            $updatedAt = new \DateTimeImmutable();
        }
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
