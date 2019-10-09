<?php

namespace Tio\Import\Import;

use DateInterval;
use DateTime;
use DOMDocument;
use DOMNode;
use DOMXPath;

class BelOrientirImport extends Import
{
    private function getDetailLinks(): array
    {
        $html = $this->gateway->sendListRequest();
        $dom = new DOMDocument;
        $dom->loadHTML($html);

        $xpath = new DomXPath($dom);
        $linkNodes = $xpath->query("//div[@id='all_item']//a[not(@class='all_data')]");

        $links = [];
        foreach ($linkNodes as $linkNode) {
            $links[] = $linkNode->getAttribute('href');
        }

        return $links;
    }

    private function getCoords(DOMXPath $xpath): array
    {
        $scripts = $xpath->query('//script');

        $coords = [];
        foreach ($scripts as $script) {
            if (preg_match('/var myTrip\s*=\s*(.*)/', $script->nodeValue, $matches)) {
                if (preg_match_all('/new\ google\.maps\.LatLng\(\s*([\d\.]*)\s*,\s*([\d\.]*)\s*\)/', $matches[1], $coordsMatches)) {
                    foreach ($coordsMatches[0] as $key => $coordsMatch) {

                        $coords[] = [
                            'lat' => $coordsMatches[1][$key],
                            'lng' => $coordsMatches[2][$key],
                        ];
                    }
                }

                break;
            }
        }

        return $coords;
    }

    private function getTours(DOMXPath $xpath, DOMNode $contentNode): array
    {
        $tours = [];

        $tableNode = $xpath->query("//table[@class='all_races']", $contentNode)->item(0);

        if (is_null($tableNode)) {
            return [];
        }

        $trNodes = $xpath->query('tbody//tr', $tableNode);

        foreach ($trNodes as $trNode) {
            $tdNodes = $xpath->query("td[not(@class='reg_days')]", $trNode);

            $datesNode = $tdNodes->item(0);
            $seatsNode = $tdNodes->item(1);
            $countDaysNode = $tdNodes->item(2);
            $priceNode = $tdNodes->item($tdNodes->length - 2);

            $toolTipNode = $xpath->query("div[@class='mytooltip']", $datesNode)->item(0);

            if ($toolTipNode != null) {
                $datesNode->removeChild($toolTipNode);
            }

            $originalPriceNode = $xpath->query("div[@class='mytooltip']//span[@class='tooltiptext mytooltip-top']", $priceNode)->item(0);

            $seats = $this->clearNodeValue($seatsNode->nodeValue);

            if (!empty($seats) && $seats != 'по запросу') {

                $dates = $this->clearNodeValue($datesNode->nodeValue);

                preg_match('/\s*(\S*)\s*-\s*(\S*)\s*/', $dates, $matchesDates);

                $from = $matchesDates[1];
                $to = $matchesDates[2];

                $from .= '.' . date('Y', strtotime($to));

                $fromDate = DateTime::createFromFormat('d.m.Y', $from);
                $toDate = DateTime::createFromFormat('d.m.Y', $to);

                if ($fromDate > $toDate) {
                    $from = $fromDate->sub(new DateInterval('P1Y'))->format('d.m.Y');
                }

                $price = $this->clearNodeValue($originalPriceNode->nodeValue);

                preg_match('/(\d*)\s*€/', $price, $matchesEUR);
                preg_match('/(\d*)\s*BYN/', $price, $matchesBYN);

                $tours[] = [
                    'date_from' => $from,
                    'date_to' => $to,
                    'seats' => $seats,
                    'countDays' => $this->clearNodeValue($countDaysNode->nodeValue),
                    'price' => $matchesEUR[1],
                    'price_service' => $matchesBYN[1] ?? '',
                ];
            }
        }

        return $tours;
    }

    private function clearNodeValue($nodeValue)
    {
        $nodeValue = trim($nodeValue);
        return preg_replace('!\s+!', ' ', $nodeValue);
    }

    private function getTourProgram(DOMXPath $xpath, DOMNode $contentNode): array
    {
        $tourProgram = [];

        $tourProgramNode = $xpath->query("div[@class='col-xs-24 col-md-16']", $contentNode)->item(0);
        $tourProgramDayNodes = $xpath->query("div[@class='day']", $tourProgramNode);
        $tourProgramBlockTourNodes = $xpath->query("div[@class='block tour']", $tourProgramNode);

        foreach ($tourProgramDayNodes as $key => $tourProgramDayNode) {
            $tourProgramBlockTourNode = $tourProgramBlockTourNodes->item($key);

            $dayDescription = $this->clearNodeValue($tourProgramDayNode->nodeValue);
            $text = $this->clearNodeValue($tourProgramBlockTourNode->nodeValue);

            $tourProgram[] = [
                'dayDescription' => $dayDescription,
                'text' => $text,
            ];
        }

        return $tourProgram;
    }

    public function import(): array
    {
        $links = $this->getDetailLinks();

        $data = [];

        array_walk($links, function ($link, $key) use (&$data) {
            preg_match('/\/tours\/bus\/(.*)\//', $link, $matches);

            $code = $matches[1];

            $html = $this->gateway->sendDetailRequest($link);

            $dom = new DOMDocument;
            $dom->loadHTML($html);

            $xpath = new DomXPath($dom);
            $contentNode = $xpath->query("//div[@class='container body__page']//div[contains(@class, 'content')]")->item(0);

            $tours = $this->getTours($xpath, $contentNode);
            if (empty($tours)) {
                return;
            }

            $nameNode = $xpath->query("//h1[@class='title']")->item(0);
            $name = $this->clearNodeValue($nameNode->nodeValue);

            $itemNodes = $xpath->query("div[@class='block_top']//div[contains(@class, 'item')]", $contentNode);

            $tourBenefitsNode = $itemNodes->item(1);

            $benefitsNodes = $xpath->query("div//ul//li", $tourBenefitsNode);

            $tourBenefits = [];
            foreach ($benefitsNodes as $benefitsNode) {
                $tourBenefits[] = $this->clearNodeValue($benefitsNode->nodeValue);
            }

            $routeNode = $itemNodes->item(2);

            $countries = $this->clearNodeValue($xpath->query("div[@class='info']//span", $routeNode)->item(0)->nodeValue);
            $cities = $this->clearNodeValue($xpath->query("div[@class='info']//span", $routeNode)->item(1)->nodeValue);

            $photoLinkNodes = $xpath->query("div[@class='info gallery']//a", $itemNodes->item(3));

            $images = [];
            foreach ($photoLinkNodes as $photoLinkNode) {
                //TODO: change link
                $images[] = 'https://www.bel-orientir.ru' . $photoLinkNode->getAttribute('href');
            }

            $coords = $this->getCoords($xpath);

            $tourProgram = $this->getTourProgram($xpath, $contentNode);

            $importantNode = $xpath->query("div[@class='col-xs-24 col-md-16']//div[@class='header_tour']//div[@class='important out']", $contentNode)->item(0);

            $importantHtml = $importantNode ? $dom->saveHTML($importantNode) : '';

            $sidebarNode = $xpath->query("div[contains(@class, 'sidebar')]", $contentNode)->item(0);

            $inPriceNodes = $xpath->query("div[@class='in']//ul//li", $sidebarNode);

            $inPrices = [];
            foreach ($inPriceNodes as $inPriceNode) {
                $inPrices[] = $this->clearNodeValue($inPriceNode->nodeValue);
            }

            $outPrices = [];
            $outPriceNodes = $xpath->query("div[@class='out']//ul//li", $sidebarNode);
            foreach ($outPriceNodes as $outPriceNode) {
                $outPrices[] = $this->clearNodeValue($outPriceNode->nodeValue);
            }


            $data[] = [
                'name' => $name,
                'code' => $code,
                'countries' => $countries,
                'cities' => $cities,
                //TODO: change link
                'link' => 'https://www.bel-orientir.ru' . $link,
                'inPrices' => $inPrices,
                'outPrices' => $outPrices,
                'importantHtml' => $importantHtml,
                'tourProgram' => $tourProgram,
                'tours' => $tours,
                'coords' => $coords,
                'images' => $images,
                'tourBenefits' => $tourBenefits,
            ];
        });

        return $data;
    }
}