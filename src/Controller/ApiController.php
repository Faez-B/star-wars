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
    public function createPlayer(int $allyCode): Response
    {
        // ----- LE JOUEUR -----

        // Vérifier si le joueur existe déjà
        $joueur = $this->em->getRepository(Joueur::class)->findOneBy(['allyCode' => $allyCode]);
        if ($joueur) return new Response('Le joueur existe déjà', Response::HTTP_OK);
        // Si le joueur existe, on n'exécute pas le code d'après

        $client = HttpClient::create();
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
        
        $this->em->persist($joueur);
        $this->em->flush();


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
            }
        }

        $this->em->flush();

        return new Response('Le joueur, sa guilde et les joueurs de la guilde sont créés', Response::HTTP_OK);
    }

    #[Route('/createHeros', name: 'create_heros', methods: ['GET'])]
    public function createHeros(HerosService $herosService): Response
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://swgoh.gg/api/characters/');
        $data = $response->toArray();

        $joueurs = $this->em->getRepository(Joueur::class)->findAll();

        foreach ($joueurs as $joueur) {
            
            // Vérifier chaque héros (API) pour chaque joueur (BDD)
            foreach ($data as $character) {
                $path = parse_url($character['url'], PHP_URL_PATH);
                $characterSlug = basename($path);
                
                $characterURL = "https://swgoh.gg/p/" . $joueur->getAllyCode() . "/characters/" . $characterSlug;

                $response = $client->request('GET', $characterURL);
                
                // On vérifie si le héros est dans la collection du joueur => (status code == 200)
                if ($response->getStatusCode() === 200) {
                    
                    $content = $response->getContent();
                    $crawler = new Crawler($content);

                    $nom            = $crawler->filter('a.pc-char-overview-name')->text();
                    $vie            = trim($herosService->getText($crawler, 'Health'));
                    $puissance      = $crawler->filter('.media-body span.pc-stat-value')->text();
                    $vitesse        = $herosService->getText($crawler, 'Speed');
                    $protection     = $herosService->getText($crawler, 'Protection');
                    $tenacite       = $herosService->getText($crawler, 'Tenacity');
                    $degatsPhys     = $herosService->getText($crawler, 'Physical Damage');
                    $degatsSpe      = $herosService->getText($crawler, 'Special Damage');
                    $chanceCCPhys   = $herosService->getText($crawler, 'Physical Critical Chance');
                    $chanceCCSpe    = $herosService->getText($crawler, 'Special Critical Chance');
                    $degatsCrit     = $herosService->getText($crawler, 'Critical Damage');
                    $volVie         = $herosService->getText($crawler, 'Health Steal');

                    // Supprime le % de la chaîne
                    $tenacite       = $herosService->removePercentageInStat($tenacite);
                    $chanceCCPhys   = $herosService->removePercentageInStat($chanceCCPhys);
                    $chanceCCSpe    = $herosService->removePercentageInStat($chanceCCSpe);
                    $degatsCrit     = $herosService->removePercentageInStat($degatsCrit);
                    $volVie         = $herosService->removePercentageInStat($volVie);

                    // Transforme la chaîne en float
                    $vie            = $herosService->convertStringToFloat($vie);
                    $protection     = $herosService->convertStringToFloat($protection);
                    $degatsPhys     = $herosService->convertStringToFloat($degatsPhys);
                    $degatsSpe      = $herosService->convertStringToFloat($degatsSpe);
                    $tenacite       = $herosService->convertStringToFloat($tenacite);
                    $chanceCCPhys   = $herosService->convertStringToFloat($chanceCCPhys);
                    $chanceCCSpe    = $herosService->convertStringToFloat($chanceCCSpe);
                    $degatsCrit     = $herosService->convertStringToFloat($degatsCrit);
                    $volVie         = $herosService->convertStringToFloat($volVie);


                    $heros = new Heros();
                    $heros->setBaseID($character['base_id']);
                    $heros->setNom($nom);
                    $heros->setVie($vie);
                    $heros->setPuissance($puissance);
                    $heros->setVitesse($vitesse);
                    $heros->setProtection($protection);
                    $heros->setTenacite($tenacite);
                    $heros->setDegatsPhysiques($degatsPhys);
                    $heros->setDegatSpeciaux($degatsSpe);
                    $heros->setChanceCCdegatsPhys($chanceCCPhys);
                    $heros->setChanceCCdegatsSpe($chanceCCSpe);
                    $heros->setDegatCritique($degatsCrit);
                    $heros->setVolVie($volVie);

                    $joueur->addHero($heros);

                    $this->em->persist($heros);
                    $this->em->persist($joueur);
                }
            }
        }

        $this->em->flush();
            
        return new Response('Les héros sont créés', Response::HTTP_OK);
    }
}
