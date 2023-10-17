<?php

namespace App\Entity;

use App\Repository\JoueurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JoueurRepository::class)]
class Joueur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $allyCode = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?int $niveau = null;

    #[ORM\Column]
    private ?int $puisGalactiqueTotale = null;

    #[ORM\Column]
    private ?int $puisGalactiqueHeros = null;

    #[ORM\Column]
    private ?int $puisGalactiqueVaisseaux = null;

    #[ORM\ManyToOne(inversedBy: 'joueurs')]
    private ?Guilde $guilde = null;

    #[ORM\OneToMany(mappedBy: 'joueur', targetEntity: Heros::class)]
    private Collection $heros;

    public function __construct()
    {
        $this->heros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAllyCode(): ?int
    {
        return $this->allyCode;
    }

    public function setAllyCode(int $allyCode): static
    {
        $this->allyCode = $allyCode;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getPuisGalactiqueTotale(): ?int
    {
        return $this->puisGalactiqueTotale;
    }

    public function setPuisGalactiqueTotale(int $puisGalactiqueTotale): static
    {
        $this->puisGalactiqueTotale = $puisGalactiqueTotale;

        return $this;
    }

    public function getPuisGalactiqueHeros(): ?int
    {
        return $this->puisGalactiqueHeros;
    }

    public function setPuisGalactiqueHeros(int $puisGalactiqueHeros): static
    {
        $this->puisGalactiqueHeros = $puisGalactiqueHeros;

        return $this;
    }

    public function getPuisGalactiqueVaisseaux(): ?int
    {
        return $this->puisGalactiqueVaisseaux;
    }

    public function setPuisGalactiqueVaisseaux(int $puisGalactiqueVaisseaux): static
    {
        $this->puisGalactiqueVaisseaux = $puisGalactiqueVaisseaux;

        return $this;
    }

    public function getGuilde(): ?Guilde
    {
        return $this->guilde;
    }

    public function setGuilde(?Guilde $guilde): static
    {
        $this->guilde = $guilde;

        return $this;
    }

    /**
     * @return Collection<int, Heros>
     */
    public function getHeros(): Collection
    {
        return $this->heros;
    }

    public function addHero(Heros $hero): static
    {
        if (!$this->heros->contains($hero)) {
            $this->heros->add($hero);
            $hero->setJoueur($this);
        }

        return $this;
    }

    public function removeHero(Heros $hero): static
    {
        if ($this->heros->removeElement($hero)) {
            // set the owning side to null (unless already changed)
            if ($hero->getJoueur() === $this) {
                $hero->setJoueur(null);
            }
        }

        return $this;
    }
}
