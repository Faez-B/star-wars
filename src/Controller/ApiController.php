<?php

namespace App\Controller;

use App\Entity\Heros;
use App\Entity\Guilde;
use App\Entity\Joueur;
use App\Service\HerosService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[Route('/{allyCode}/guild', name: 'guild_infos', methods: ['GET'])]
    public function getGuildInfos(int $allyCode): JsonResponse
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://swgoh.gg/api/player/' . $allyCode);
        $data = $response->toArray();

        // $récupérer l'id de la guilde grâce à l'allyCode du joueur
        $guildID = $data["data"]["guild_id"];

        $response = $client->request('GET', 'https://swgoh.gg/api/guild-profile/' . $guildID);
        $data = ($response->toArray())['data'];

        return $this->json([
            'id' => $data['guild_id'],
            'nom' => $data['name'],
            'puisGalactique' => $data['galactic_power'],
            'nbJoueurs' => count($data["members"]),
        ]);
    }

    #[Route('/{allyCode}/create', name: 'create_player')]
    public function createPlayer(int $allyCode, HerosService $herosService): Response
    {
        // ----- LE JOUEUR -----

        // Vérifier si le joueur existe déjà
        $joueur = $this->em->getRepository(Joueur::class)->findOneBy(['allyCode' => $allyCode]);
        if ($joueur) return new Response('Le joueur existe déjà', Response::HTTP_OK);
        // Si le joueur existe, on n'exécute pas le code d'après

        $client = HttpClient::create();
        $response = $client->request('GET', 'https://swgoh.gg/api/player/' . $allyCode);
        $data = ($response->toArray())['data'];
        $characters = ($response->toArray())['units'];

        $joueur = new Joueur();
        $joueur->setAllyCode($allyCode);
        $joueur->setPseudo($data['name']);
        $joueur->setTitre($data['title']);
        $joueur->setNiveau($data['level']);
        $joueur->setPuisGalactiqueTotale($data['galactic_power']);
        $joueur->setPuisGalactiqueHeros($data['character_galactic_power']);
        $joueur->setPuisGalactiqueVaisseaux($data['ship_galactic_power']);
        
        $this->em->persist($joueur);
        $this->em->flush();

        // La collection de héros du joueur
        $herosService->addHeros($joueur, $characters);

        // ----- LA GUILDE -----

        // $récupérer l'id de la guilde grâce à l'allyCode du joueur
        $guildID = $data["guild_id"];

        // Vérifier si la guilde existe déjà
        $guilde = $this->em->getRepository(Guilde::class)->findOneBy(['uniqId' => $guildID]);
        if ($guilde) return new Response('La guilde existe déjà', Response::HTTP_OK);
        // Si la guilde existe, on n'exécute pas le code d'après

        $response = $client->request('GET', 'https://swgoh.gg/api/guild-profile/' . $guildID);
        $data = ($response->toArray())['data'];

        $guilde = new Guilde();
        $guilde->setUniqId($data['guild_id']);
        $guilde->setNom($data['name']);
        $guilde->setPuisGalactique($data['galactic_power']);
        $guilde->addJoueur($joueur);

        $this->em->persist($guilde);


        // ----- LES JOUEURS de la guilde -----
        $otherMembersAllyCode = [];

        foreach ($data["members"] as $member) {
            $allyCode = $member["ally_code"];

            // Vérifier si le joueur existe déjà
            $joueur = $this->em->getRepository(Joueur::class)->findOneBy(['allyCode' => $allyCode]);

            if ( !$joueur ) {
                $response = $client->request('GET', 'https://swgoh.gg/api/player/' . $allyCode);
                $data = ($response->toArray())['data'];
    
                $joueur = new Joueur();
                $joueur->setAllyCode($allyCode);
                $joueur->setPseudo($data['name']);
                $joueur->setTitre($data['title']);
                $joueur->setNiveau($data['level']);
                $joueur->setPuisGalactiqueTotale($data['galactic_power']);
                $joueur->setPuisGalactiqueHeros($data['character_galactic_power']);
                $joueur->setPuisGalactiqueVaisseaux($data['ship_galactic_power']);
                
                $guilde->addJoueur($joueur);
                $this->em->persist($joueur);
                $this->em->persist($guilde);

                $otherMembersAllyCode[] = $allyCode;
            }
        }

        $this->em->flush();
        $this->em->clear();

        // ----- LES HÉROS des joueurs de la guilde -----
        foreach ($otherMembersAllyCode as $allyCode) {
            $response = $client->request('GET', 'https://swgoh.gg/api/player/' . $allyCode);
            $characters = ($response->toArray())['units'];

            $joueur = $this->em->getRepository(Joueur::class)->findOneBy(['allyCode' => $allyCode]);
            $herosService->addHeros($joueur, $characters);
            $this->em->clear();
        }

        return new Response('Le joueur, sa guilde et les joueurs de la guilde sont créés', Response::HTTP_OK);
    }
}
