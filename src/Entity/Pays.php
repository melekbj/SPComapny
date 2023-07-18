<?php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[Vich\Uploadable]
#[UniqueEntity(fields: ['nom'], message: 'There is already a libelle with this name')]

class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Vich\UploadableField(mapping: 'etat_image', fileNameProperty: 'photo')]
    public ?File $imageFile = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'pays', targetEntity: Banques::class,  cascade:['remove'])]
    private Collection $banques;

    public function __construct()
    {
        $this->banques = new ArrayCollection();
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

    /**
     * @return Collection<int, Banques>
     */
    public function getBanques(): Collection
    {
        return $this->banques;
    }

    public function addBanque(Banques $banque): static
    {
        if (!$this->banques->contains($banque)) {
            $this->banques->add($banque);
            $banque->setPays($this);
        }

        return $this;
    }

    public function removeBanque(Banques $banque): static
    {
        if ($this->banques->removeElement($banque)) {
            // set the owning side to null (unless already changed)
            if ($banque->getPays() === $this) {
                $banque->setPays(null);
            }
        }

        return $this;
    }
}
