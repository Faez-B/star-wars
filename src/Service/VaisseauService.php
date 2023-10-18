<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class VaisseauService
{
    public function getText(Crawler $crawler, string $title): string
    {
        $spanCount = $crawler->filterXPath('//span[contains(text(), "' . $title . '")]')->count();
        if ($spanCount > 0) {
            return $crawler->filter('.unit-stat-group-stat')->filterXPath('//span[contains(text(), "' . $title . '")]/following-sibling::span/text()')->text();
        }

        return '';
    }

    public function removePercentageInStat(string $stat) : string 
    {
        return str_replace('%', '', $stat);
    }

    public function convertStringToFloat(string $stat) : float
    {
        return floatval(str_replace(',', '.', $stat));
    }

    public function checkTitleSpanExist(Crawler $crawler, string $title): bool
    {
        $spanCount = $crawler->filterXPath('//span[contains(text(), "' . $title . '")]')->count();
        return $spanCount > 0;
    }

}
