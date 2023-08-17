<?php

namespace App\Entity;

use App\Repository\BanquesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: BanquesRepository::class)]
#[Vich\Uploadable]
#[UniqueEntity(fields: ['nom'], message: 'There is already a libelle with this name')]
#[UniqueEntity(fields: ['mail'], message: 'There is already a mail with this address')]
#[UniqueEntity(fields: ['tel'], message: 'There is already a tel with this number')]

class Banques
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'banques')]
    private ?Pays $pays = null;

    #[Vich\UploadableField(mapping: 'banque_image', fileNameProperty: 'photo')]
    public ?File $imageFile = null;

    #[ORM\Column(length: 255,nullable:true)]
    private ?string $photo = 'photo';

    #[ORM\OneToMany(mappedBy: 'banque', targetEntity: Commande::class, cascade:['remove'])]
    private Collection $commandes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true, unique:true)]
    private ?string $tel = null;

    #[ORM\Column(length: 255, nullable: true, unique:true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $responsable = null;

    #[ORM\OneToMany(mappedBy: 'banques', targetEntity: Compte::class)]
    private Collection $compte;


    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->compte = new ArrayCollection();
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

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;

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
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setBanque($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getBanque() === $this) {
                $commande->setBanque(null);
            }
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            // It's important to update the "updatedAt" field to trigger the upload
            $this->updatedAt = new \DateTimeImmutable();
        }
    }


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getDescr(): ?string
    {
        return $this->descr;
    }

    public function setDescr(?string $descr): static
    {
        $this->descr = $descr;

        return $this;
    }

    public function getResponsable(): ?string
    {
        return $this->responsable;
    }

    public function setResponsable(?string $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * @return Collection<int, Compte>
     */
    public function getCompte(): Collection
    {
        return $this->compte;
    }

    public function addCompte(Compte $compte): static
    {
        if (!$this->compte->contains($compte)) {
            $this->compte->add($compte);
            $compte->setBanques($this);
        }

        return $this;
    }

    public function removeCompte(Compte $compte): static
    {
        if ($this->compte->removeElement($compte)) {
            // set the owning side to null (unless already changed)
            if ($compte->getBanques() === $this) {
                $compte->setBanques(null);
            }
        }

        return $this;
    }

    
}
