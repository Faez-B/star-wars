<?php

namespace App\Entity;

use App\Repository\JoueurRepository;
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
}
