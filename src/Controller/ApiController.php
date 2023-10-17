<?php

namespace App\Controller;

use App\Entity\Guilde;
use App\Entity\Joueur;
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
    public function createHeros(): JsonResponse
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://swgoh.gg/api/characters/');
        $data = $response->toArray();

        $joueurs = $this->em->getRepository(Joueur::class)->findAll();

        foreach ($joueurs as $joueur) {
            dump($joueur->getPseudo());
            
            // Vérifier chaque héros pour chaque joueur
            foreach ($data as $character) {
                $path = parse_url($character['url'], PHP_URL_PATH);
                $characterSlug = basename($path);
                
                $characterURL = "https://swgoh.gg/p/" . $joueur->getAllyCode() . "/characters/" . $characterSlug;

                $response = $client->request('GET', $characterURL);
                
                // On vérifie si le héros est dans la collection du joueur => (status code == 200)
                if ($response->getStatusCode() === 200) {
                    
                    $content = $response->getContent();
                    $crawler = new Crawler($content);

                    $nom = $crawler->filter('a.pc-char-overview-name')->text();
                    $vie = trim($crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Health")]/following-sibling::span/text()')->text());
                    $puissance = $crawler->filter('.media-body span.pc-stat-value')->text();
                    $vitesse = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Speed")]/following-sibling::span/text()')->text();
                    $protection = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Protection")]/following-sibling::span/text()')->text();
                    $tenacite = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Tenacity")]/following-sibling::span/text()')->text();
                    $degatsPhys = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Physical Damage")]/following-sibling::span/text()')->text();
                    $degatsSpe = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Special Damage")]/following-sibling::span/text()')->text();
                    $chanceCCPhys = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Physical Critical Chance")]/following-sibling::span/text()')->text();
                    $chanceCCSpe = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Special Critical Chance")]/following-sibling::span/text()')->text();
                    $degatsCrit = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Critical Damage")]/following-sibling::span/text()')->text();
                    $volVie = $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "Health Steal")]/following-sibling::span/text()')->text();

                    // Supprime le % de la chaîne
                    $tenacite = str_replace('%', '', $tenacite);
                    $chanceCCPhys = str_replace('%', '', $chanceCCPhys);
                    $chanceCCSpe = str_replace('%', '', $chanceCCSpe);
                    $degatsCrit = str_replace('%', '', $degatsCrit);
                    $volVie = str_replace('%', '', $volVie);

                    // Transforme la chaîne en float
                    $vie = str_replace(',', '.', $vie);                     $vie = floatval($vie);
                    $protection = str_replace(',', '.', $protection);       $protection = floatval($protection);
                    $degatsPhys = str_replace(',', '.', $degatsPhys);       $degatsPhys = floatval($degatsPhys);
                    $degatsSpe = str_replace(',', '.', $degatsSpe);         $degatsSpe = floatval($degatsSpe);
                    $tenacite = str_replace(',', '.', $tenacite);           $tenacite = floatval($tenacite);
                    $chanceCCPhys = str_replace(',', '.', $chanceCCPhys);   $chanceCCPhys = floatval($chanceCCPhys);
                    $chanceCCSpe = str_replace(',', '.', $chanceCCSpe);     $chanceCCSpe = floatval($chanceCCSpe);
                    $degatsCrit = str_replace(',', '.', $degatsCrit);       $degatsCrit = floatval($degatsCrit);
                    $volVie = str_replace(',', '.', $volVie);               $volVie = floatval($volVie);
                    
                    dump([
                        'nom' => $nom,
                        'puissance' => intval($puissance),
                        'vitesse' => intval($vitesse),
                        'vie' => $vie,
                        'protection' => $protection,
                        'degatsPhys' => $degatsPhys,
                        'degatsSpe' => $degatsSpe,
                        'tenacite' => $tenacite,
                        'chanceCCPhys' => $chanceCCPhys,
                        'chanceCCSpe' => $chanceCCSpe,
                        'degatsCrit' => $degatsCrit,
                        'volVie' => $volVie,
                    ]);
                }
            }

            exit;
        }
            
        return $this->json($data);
    }
}
