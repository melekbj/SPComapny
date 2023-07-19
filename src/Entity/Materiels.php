<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MaterielsRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MaterielsRepository::class)]
#[Vich\Uploadable]
class Materiels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[Vich\UploadableField(mapping: 'materiels_image', fileNameProperty: 'photo')]
    public ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $photo = null;

    #[ORM\Column]
    private ?float $pu = null;

    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: CommandeMateriels::class)]
    private Collection $commandeMateriels;

    #[ORM\Column(nullable: true)]
    private ?float $tauxtva = null;

    
    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->commandeMateriels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPu(): ?float
    {
        return $this->pu;
    }

    public function setPu(float $pu): static
    {
        $this->pu = $pu;

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
            $commandeMateriel->setMateriel($this);
        }

        return $this;
    }

    public function removeCommandeMateriel(CommandeMateriels $commandeMateriel): static
    {
        if ($this->commandeMateriels->removeElement($commandeMateriel)) {
            // set the owning side to null (unless already changed)
            if ($commandeMateriel->getMateriel() === $this) {
                $commandeMateriel->setMateriel(null);
            }
        }

        return $this;
    }

    public function getTauxtva(): ?float
    {
        return $this->tauxtva;
    }

    public function setTauxtva(?float $tauxtva): static
    {
        $this->tauxtva = $tauxtva;

        return $this;
    }



    
    






}
