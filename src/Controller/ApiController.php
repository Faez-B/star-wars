<?php

namespace App\Controller;

use App\Entity\Heros;
use App\Entity\Guilde;
use App\Entity\Joueur;
use App\Entity\Vaisseau;
use App\Service\HerosService;
use App\Service\VaisseauService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Pour l'ajout de héros des joueurs de la guilde
ini_set('memory_limit', '512M');

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

        // Si on n'augmente pas la mémoire -> Error : Memory limit
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

    #[Route('/{allyCode}/createShips', name: 'create_ships', methods: ['GET'])]
    public function createShips(int $allyCode, VaisseauService $service): Response
    {
        // Vérifier si le joueur existe
        $joueur = $this->em->getRepository(Joueur::class)->findOneBy(['allyCode' => $allyCode]);

        if (!$joueur) return new Response('Le joueur n\'existe pas', Response::HTTP_OK);

        // Vérifier si les vaisseaux du joueur existent déjà
        if (count($joueur->getVaisseaux()) > 0) return new Response('Les vaisseaux existent déjà', Response::HTTP_OK);

        $client = HttpClient::create();
        $response = $client->request('GET', 'https://swgoh.gg/api/ships/');
        $data = $response->toArray();

        foreach ($data as $ship) {
            $path = parse_url($ship['url'], PHP_URL_PATH);
            $shipSlug = basename($path);

            $shipUrlResponse = $client->request('GET', "https://swgoh.gg/p/" . $joueur->getAllyCode() . "/ships/" . $shipSlug);
            
            // On vérifie si le héros est dans la collection du joueur => (status code == 200)
            if ($shipUrlResponse->getStatusCode() === 200) {
                
                $content = $shipUrlResponse->getContent();
                $crawler = new Crawler($content);

                $nom            = $crawler->filter('a.pc-char-overview-name')->text();
                $vie            = trim($service->getText($crawler, 'Health'));
                $puissance      = $crawler->filter('.unit-stat-group-stat-value')->first()->text();
                $vitesse        = $service->getText($crawler, 'Speed');
                $protection     = $service->getText($crawler, 'Protection');
                $tenacite       = $service->getText($crawler, 'Tenacity');
                $degatsPhys     = $service->getText($crawler, 'Physical Damage');
                $degatsSpe      = $service->getText($crawler, 'Special Damage');
                $chanceCCPhys   = $service->getText($crawler, 'Physical Critical Chance');
                $chanceCCSpe    = $service->getText($crawler, 'Special Critical Chance');

                // Supprime le % de la chaîne
                $tenacite       = $service->removePercentageInStat($tenacite);
                $chanceCCPhys   = $service->removePercentageInStat($chanceCCPhys);
                $chanceCCSpe    = $service->removePercentageInStat($chanceCCSpe);

                // Transforme la chaîne en float
                $vie            = $service->convertStringToFloat($vie);
                $protection     = $service->convertStringToFloat($protection);
                $degatsPhys     = $service->convertStringToFloat($degatsPhys);
                $degatsSpe      = $service->convertStringToFloat($degatsSpe);
                $tenacite       = $service->convertStringToFloat($tenacite);
                $chanceCCPhys   = $service->convertStringToFloat($chanceCCPhys);
                $chanceCCSpe    = $service->convertStringToFloat($chanceCCSpe);


                $vaisseau = new Vaisseau();
                $vaisseau->setBaseID($ship['base_id']);
                $vaisseau->setNom($nom);
                $vaisseau->setVie($vie);
                $vaisseau->setPuissance($puissance);
                $vaisseau->setVitesse($vitesse);
                $vaisseau->setProtection($protection);
                $vaisseau->setTenacite($tenacite);
                $vaisseau->setDegatsPhysiques($degatsPhys);
                $vaisseau->setDegatSpeciaux($degatsSpe);
                $vaisseau->setChanceCCdegatsPhys($chanceCCPhys);
                $vaisseau->setChanceCCdegatsSpe($chanceCCSpe);

                $joueur->addVaisseau($vaisseau);

                $this->em->persist($vaisseau);
                $this->em->persist($joueur);

                
            }
        }
        
        $this->em->flush();
        $this->addFlash('success', 'Les vaisseaux ont été ajoutés');

        return $this->redirectToRoute('see_player', ['id' => $joueur->getId()]);
    }
}
