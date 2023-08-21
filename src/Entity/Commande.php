<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[UniqueEntity(fields: ['ref'], message: 'This ref is already exists')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;


    #[ORM\Column]
    #[Assert\NotBlank(message: 'The tauxtva field cannot be blank.')]
    #[Assert\Type(type : 'float', message : "The tauxtva field must be a float.")]
    private ?float $tauxtva = null;

    #[ORM\Column]
    #[Assert\Type(type : 'integer', message : "The avance field must be an integer.")]
    private ?int $avance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat = 'pending';

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Banques $banque = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeMateriels::class, cascade:['remove'])]
    private Collection $commandeMateriels;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(unique:true)]
    private ?string $ref = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Devise $devise = null;

    #[ORM\Column(nullable: true)]
    private ?float $remise = null;


    public function __construct()
    {
        $this->materiel = new ArrayCollection();
        $this->commandeMateriels = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }


    public function getTauxtva(): ?float
    {
        return $this->tauxtva;
    }

    public function setTauxtva(float $tauxtva): static
    {
        $this->tauxtva = $tauxtva;

        return $this;
    }

    public function getAvance(): ?int
    {
        return $this->avance;
    }

    public function setAvance(int $avance): static
    {
        $this->avance = $avance;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): static
    {
        $this->etat = $etat;

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

    /**
     * @return Collection<int, CommandeMateriels>
     */
    public function getCommandeMateriels(): Collection
    {
        return $this->commandeMateriels;
    }

    public function addCommandeMateriel(CommandeMateriels $commandeMateriel): static
    {
        if (!$this->commandeMateriels->contains($commandeMateriel)) {
            $this->commandeMateriels->add($commandeMateriel);
            $commandeMateriel->setCommande($this);
        }

        return $this;
    }

    public function removeCommandeMateriel(CommandeMateriels $commandeMateriel): static
    {
        if ($this->commandeMateriels->removeElement($commandeMateriel)) {
            // set the owning side to null (unless already changed)
            if ($commandeMateriel->getCommande() === $this) {
                $commandeMateriel->setCommande(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }



    public function hasMateriel($materiel)
{
    foreach ($this->getCommandeMateriels() as $commandeMateriel) {
        if ($commandeMateriel->getMateriel() === $materiel) {
            return true;
        }
    }

    return false;
}

public function getMaterielQuantity($materiel)
{
    foreach ($this->getCommandeMateriels() as $commandeMateriel) {
        if ($commandeMateriel->getMateriel() === $materiel) {
            return $commandeMateriel->getQuantite();
        }
    }

    return null;
}

public function getUser(): ?User
{
    return $this->user;
}

public function setUser(?User $user): static
{
    $this->user = $user;

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
