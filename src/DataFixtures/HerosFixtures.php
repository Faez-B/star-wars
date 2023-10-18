<?php

namespace App\DataFixtures;

use App\Entity\Heros;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\HttpClient\HttpClient;

class HerosFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $client = HttpClient::create();
        // $response = $client->request('GET', 'https://swgoh.gg/api/characters/');
        // $data = ($response->toArray())['data'];

        // foreach ($data as $character) {


        //     // $hero = new Heros();
        //     // $hero->setNom($character['name']);
        //     // $hero->setBaseID($character['base_id']);
        //     // $hero->setPuissance($character['power']);

        //     // $manager->persist($hero);
        // }

        $manager->flush();
    }
}
