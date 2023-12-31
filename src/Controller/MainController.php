<?php

namespace App\Controller;

use App\Entity\Guilde;
use App\Entity\Joueur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[Route('/', name: 'main')]
    public function index(): Response
    {
        $joueurs = $this->em->getRepository(Joueur::class)->findAll();

        return $this->render('main/index.html.twig', [
            'joueurs' => $joueurs,
        ]);
    }

    #[Route('/joueur/{id}', name: 'see_player')]
    public function seePlayer(Joueur $joueur): Response
    {
        $addShips = false;
        if ($joueur->getVaisseaux()->count() === 0) {
            $addShips = true;
        }

        return $this->render('main/joueur.html.twig', [
            'joueur' => $joueur,
            'addShips' => $addShips,
        ]);
    }

    #[Route('/guilde/{id}', name: 'see_guild')]
    public function seeGuild(Guilde $guilde): Response
    {
        return $this->render('main/guilde.html.twig', [
            'guilde' => $guilde,
        ]);
    }
}
