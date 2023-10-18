<?php

namespace App\Service;

use App\Entity\Heros;
use App\Entity\Joueur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class HerosService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function getText(Crawler $crawler, string $title): string
    {
        return $crawler->filter('.media-body')->filterXPath('//span[contains(text(), "' . $title . '")]/following-sibling::span/text()')->text();
    }

    public function removePercentageInStat(string $stat) : string 
    {
        return str_replace('%', '', $stat);
    }

    public function convertStringToFloat(string $stat) : float
    {
        return floatval(str_replace(',', '.', $stat));
    }

    public function addHeros(Joueur $joueur, array $characters) 
    {
        foreach ($characters as $character) {
            $characterData = $character['data'];
            $stats = $characterData['stats'];

            $nom            = $characterData['name'];
            $puissance      = $characterData['power'];
            $vie            = $stats[1];
            $vitesse        = $stats[5];
            $protection     = $stats[28];
            $tenacite       = $stats[18];
            $degatsPhys     = $stats[6];
            $degatsSpe      = $stats[7];
            $chanceCCPhys   = $stats[14];
            $chanceCCSpe    = $stats[15];
            $degatsCrit     = $stats[16];
            $volVie         = $stats[27];

            $heros = new Heros();
            $heros->setBaseID($characterData['base_id']);
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

        $this->em->flush();
    }
}
