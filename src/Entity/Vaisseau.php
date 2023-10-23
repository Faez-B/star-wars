<?php

namespace App\Entity;

use App\Repository\VaisseauRepository;
use App\Trait\BaseJoueurCollectionTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VaisseauRepository::class)]
class Vaisseau
{
    use BaseJoueurCollectionTrait;
}
