<?php

namespace App\Entity;

use App\Repository\DeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviseRepository::class)]
class Devise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Tresorie::class)]
    private Collection $tresories;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: TresorieHistory::class)]
    private Collection $tresorieHistories;

    // #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Tresorie::class)]
    // private Collection $tresories;

    public function __construct()
    {
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
            $tresory->setDevise($this);
        }

        return $this;
    }

    public function removeTresory(Tresorie $tresory): static
    {
        if ($this->tresories->removeElement($tresory)) {
            // set the owning side to null (unless already changed)
            if ($tresory->getDevise() === $this) {
                $tresory->setDevise(null);
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
            $tresorieHistory->setDevise($this);
        }

        return $this;
    }

    public function removeTresorieHistory(TresorieHistory $tresorieHistory): static
    {
        if ($this->tresorieHistories->removeElement($tresorieHistory)) {
            // set the owning side to null (unless already changed)
            if ($tresorieHistory->getDevise() === $this) {
                $tresorieHistory->setDevise(null);
            }
        }

        return $this;
    }
}
