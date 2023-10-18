<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class HerosService
{
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
}
