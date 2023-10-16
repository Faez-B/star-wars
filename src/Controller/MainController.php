<?php

namespace App\Controller;

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
}
