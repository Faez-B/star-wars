<?php

namespace App\Entity;

use App\Repository\VaisseauRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VaisseauRepository::class)]
class Vaisseau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $baseID = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $vie = null;

    #[ORM\Column]
    private ?float $protection = null;

    #[ORM\Column]
    private ?int $puissance = null;

    #[ORM\Column]
    private ?float $tenacite = null;

    #[ORM\Column]
    private ?float $degatsPhysiques = null;

    #[ORM\Column]
    private ?float $degatSpeciaux = null;

    #[ORM\Column]
    private ?float $chanceCCdegatsPhys = null;

    #[ORM\Column]
    private ?float $chanceCCdegatsSpe = null;

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

    public function getBaseID(): ?string
    {
        return $this->baseID;
    }

    public function setBaseID(string $baseID): static
    {
        $this->baseID = $baseID;

        return $this;
    }

    public function getVie(): ?string
    {
        return $this->vie;
    }

    public function setVie(string $vie): static
    {
        $this->vie = $vie;

        return $this;
    }

    public function getProtection(): ?float
    {
        return $this->protection;
    }

    public function setProtection(float $protection): static
    {
        $this->protection = $protection;

        return $this;
    }

    public function getPuissance(): ?int
    {
        return $this->puissance;
    }

    public function setPuissance(int $puissance): static
    {
        $this->puissance = $puissance;

        return $this;
    }

    public function getTenacite(): ?float
    {
        return $this->tenacite;
    }

    public function setTenacite(float $tenacite): static
    {
        $this->tenacite = $tenacite;

        return $this;
    }

    public function getDegatsPhysiques(): ?float
    {
        return $this->degatsPhysiques;
    }

    public function setDegatsPhysiques(float $degatsPhysiques): static
    {
        $this->degatsPhysiques = $degatsPhysiques;

        return $this;
    }

    public function getDegatSpeciaux(): ?float
    {
        return $this->degatSpeciaux;
    }

    public function setDegatSpeciaux(float $degatSpeciaux): static
    {
        $this->degatSpeciaux = $degatSpeciaux;

        return $this;
    }

    public function getChanceCCdegatsPhys(): ?float
    {
        return $this->chanceCCdegatsPhys;
    }

    public function setChanceCCdegatsPhys(float $chanceCCdegatsPhys): static
    {
        $this->chanceCCdegatsPhys = $chanceCCdegatsPhys;

        return $this;
    }

    public function getChanceCCdegatsSpe(): ?float
    {
        return $this->chanceCCdegatsSpe;
    }

    public function setChanceCCdegatsSpe(float $chanceCCdegatsSpe): static
    {
        $this->chanceCCdegatsSpe = $chanceCCdegatsSpe;

        return $this;
    }
}
