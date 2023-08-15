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

    #[ORM\OneToMany(mappedBy: 'banque', targetEntity: Tresorie::class)]
    private Collection $tresories;

    #[ORM\OneToMany(mappedBy: 'banque', targetEntity: TresorieHistory::class)]
    private Collection $tresorieHistories;

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

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
        $this->tresories = new ArrayCollection();
        $this->tresorieHistories = new ArrayCollection();
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

    /**
     * @return Collection<int, Tresorie>
     */
    public function getTresories(): Collection
    {
        return $this->tresories;
    }

    public function addTresory(Tresorie $tresory): static
    {
        if (!$this->tresories->contains($tresory)) {
            $this->tresories->add($tresory);
            $tresory->setBanque($this);
        }

        return $this;
    }

    public function removeTresory(Tresorie $tresory): static
    {
        if ($this->tresories->removeElement($tresory)) {
            // set the owning side to null (unless already changed)
            if ($tresory->getBanque() === $this) {
                $tresory->setBanque(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TresorieHistory>
     */
    public function getTresorieHistories(): Collection
    {
        return $this->tresorieHistories;
    }

    public function addTresorieHistory(TresorieHistory $tresorieHistory): static
    {
        if (!$this->tresorieHistories->contains($tresorieHistory)) {
            $this->tresorieHistories->add($tresorieHistory);
            $tresorieHistory->setBanque($this);
        }

        return $this;
    }

    public function removeTresorieHistory(TresorieHistory $tresorieHistory): static
    {
        if ($this->tresorieHistories->removeElement($tresorieHistory)) {
            // set the owning side to null (unless already changed)
            if ($tresorieHistory->getBanque() === $this) {
                $tresorieHistory->setBanque(null);
            }
        }

        return $this;
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


}
