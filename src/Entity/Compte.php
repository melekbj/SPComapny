<?php

namespace App\Entity;

use App\Repository\CompteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: CompteRepository::class)]
#[UniqueEntity(fields: ['num'], message: 'Ce numéro du compte existe déjà')]
class Compte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique:true)]
    private ?string $num = null;

    #[ORM\ManyToOne(inversedBy: 'comptes')]
    private ?Devise $devise = null;

    #[ORM\Column]
    private ?float $solde = null;

    #[ORM\ManyToOne(inversedBy: 'compte')]
    private ?Banques $banques = null;

    #[ORM\OneToMany(mappedBy: 'compte', targetEntity: Operation::class)]
    private Collection $operation;

    public function __construct()
    {
        $this->operation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNum(): ?string
    {
        return $this->num;
    }

    public function setNum(string $num): static
    {
        $this->num = $num;

        return $this;
    }

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(?Devise $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(float $solde): static
    {
        $this->solde = $solde;

        return $this;
    }

    public function getBanques(): ?Banques
    {
        return $this->banques;
    }

    public function setBanques(?Banques $banques): static
    {
        $this->banques = $banques;

        return $this;
    }

    /**
     * @return Collection<int, Operation>
     */
    public function getOperation(): Collection
    {
        return $this->operation;
    }

    public function addOperation(Operation $operation): static
    {
        if (!$this->operation->contains($operation)) {
            $this->operation->add($operation);
            $operation->setCompte($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): static
    {
        if ($this->operation->removeElement($operation)) {
            // set the owning side to null (unless already changed)
            if ($operation->getCompte() === $this) {
                $operation->setCompte(null);
            }
        }

        return $this;
    }
}
