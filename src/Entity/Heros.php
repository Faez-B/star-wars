<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\HerosRepository;
use App\Trait\BaseJoueurCollectionTrait;

#[ORM\Entity(repositoryClass: HerosRepository::class)]
class Heros
{
    use BaseJoueurCollectionTrait;

    #[ORM\Column]
    private ?float $degatCritique = null;

    #[ORM\Column]
    private ?float $volVie = null;

    public function getDegatCritique(): ?float
    {
        return $this->degatCritique;
    }

    public function setDegatCritique(float $degatCritique): static
    {
        $this->degatCritique = $degatCritique;

        return $this;
    }

    public function getVolVie(): ?float
    {
        return $this->volVie;
    }

    public function setVolVie(float $volVie): static
    {
        $this->volVie = $volVie;

        return $this;
    }
}
