<?php

namespace Tio\Import\Export;

use _CIBElement;
use CFile;
use CIBlockElement;
use CModule;
use Tio\Import\Store\BusTour;
use Tio\Import\Store\HLPrice;
use travelsoft\currency\factory\Converter;


class IBlockExport extends Export
{
    /**
     * @param array $data = [
     *   'name' => '',
     *   'code' => '',
     *   'countries' => '',
     *   'cities' => '',
     *   'link' => '',
     *   'inPrices' => [],
     *   'outPrices' => [],
     *   'importantHtml' => '',
     *   'tourProgram' => [],
     *   'tours' => [],
     *   'coords' => [],
     *   'images' => [],
     *   'tourBenefits' => [],
     * ]
     */
    public function export(array $data)
    {
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('travelsoft.currency');

        foreach ($data as $item) {
            $busTour = BusTour::first(['filter' => ['PROPERTY_SOURCE' => $item['link']]]);

            if (!empty($busTour)) {
                $prices = HLPrice::get(['filter' => ['UF_XML_ID' => $busTour['PROPERTIES']['PRICES']['VALUE']]]);

                array_walk($prices, function (array $price) {
                    HLPrice::delete($price['ID']);
                });
            }

            $prices = [];
            array_walk($item['tours'], function ($tour) use (&$prices) {
                $xmlId = randString(8);
                HLPrice::add([
                    'UF_DATE_FROM' => $tour['date_from'],
                    'UF_DATE_TO' => $tour['date_to'],
                    'UF_SEATS' => $tour['seats'],
                    'UF_COUNT_DAYS' => $tour['countDays'],
                    'UF_PRICE' => $tour['price'],
                    'UF_PRICE_SERVICE' => $tour['price_service'],
                    'UF_XML_ID' => $xmlId,
                ]);

                $prices[] = $xmlId;
            });

            $properties = $this->getProperties($item);
            $properties['PRICES'] = $prices;

            if (!empty($busTour)) {


                /*$properties = [];
                $properties['PRICES'] = $prices;
                $properties['ROUTE'] = $busTour['PROPERTIES']['ROUTE']['VALUE'];
                $properties['SOURCE'] = $busTour['PROPERTIES']['SOURCE']['VALUE'];
                $properties['PRICE_INCLUDE'] = $busTour['PROPERTIES']['PRICE_INCLUDE']['VALUE'];
                $properties['PRICE_NO_INCLUDE'] = $busTour['PROPERTIES']['PRICE_NO_INCLUDE']['VALUE'];
                $properties['ADDITIONAL'] = $busTour['PROPERTIES']['ADDITIONAL']['VALUE'];
                $properties['PICTURES'] = $busTour['PROPERTIES']['PICTURES']['VALUE'];
                $properties['PROVIDER'] = $busTour['PROPERTIES']['PROVIDER']['VALUE'];
                $properties['CHIPS'] = $busTour['PROPERTIES']['CHIPS']['VALUE'];


                $days = [];

                array_walk($busTour['PROPERTIES']['NDAYS']['VALUE'], function ($day, $key) use (&$days, $busTour) {
                    $days["n{$key}"] = [
                        "VALUE" => $day['TEXT'],
                        "DESCRIPTION" => $busTour['PROPERTIES']['NDAYS']['DESCRIPTION'][$key],
                    ];
                });

                $properties['NDAYS'] = $days;*/

                $updatedProperties = [
                    'PRICES' => $properties['PRICES'],
                ];

                BusTour::updateProperties($busTour['ID'], $updatedProperties);

                dm(['id' => $busTour['ID'], '$prices' => $prices]);
            } else {
                $id = BusTour::add([
                    'NAME' => $item['name'],
                    'CODE' => $item['code'],
                    'PROPERTY_VALUES' => $properties
                ]);

                dm(['$id' => $id]);
            }
        }
    }

    private function getProperties(array $item): array
    {
        $inPrices = $this->generatePrices($item['inPrices']);
        $outPrices = $this->generatePrices($item['outPrices']);
        $tourProgram = $this->generateTourProgram($item['tourProgram']);
        $images = $this->getImages($item['images']);

        $datesFrom = array_column($item['tours'], 'date_from');

        $converter = Converter::getInstance();

        $minPrice = array_reduce($item['tours'], function ($result, $item) use ($converter) {

            $price = $converter->convert($item['price'], "EUR", 'BYN')->getResultLikeArray();
            $value = $price['price'];
            $value += $item['price_service'];

            return is_null($result) || $value < $result ? $value : $result;
        });

        return [
            'ROUTE' => $item['cities'],
            'SOURCE' => $item['link'],
            'PRICE_INCLUDE' => $inPrices,
            'PRICE_NO_INCLUDE' => $outPrices,
            'ADDITIONAL' => $item['importantHtml'],
            'NDAYS' => $tourProgram,
            'PICTURES' => $images,
            'CHIPS' => $item['tourBenefits'],
            'PROVIDER' => $this->getProviderId(),
            'TRANSPORT' => $this->getTransportTypeId(),
            'DEPARTURE' => $datesFrom,
            'PRICE' => $minPrice,
            'CURRENCY' => $this->getCurrencyId(),
        ];
    }

    private function getTransportTypeId(): int
    {
        return 21095;
    }

    private function getProviderId(): int
    {
        return 63672;
    }

    private function getCurrencyId(): int
    {
        return 153;
    }

    private function generatePrices($prices)
    {
        $prices = array_map(function ($price) {
            return "<li>{$price}</li>";
        }, $prices);

        $prices = implode($prices);

        return "<ul>{$prices}</ul>";
    }

    private function generateTourProgram(array $tourProgram): array
    {
        $days = [];

        array_walk($tourProgram, function ($day, $key) use (&$days) {
            $days["n{$key}"] = [
                "VALUE" => $day['text'],
                "DESCRIPTION" => $day['dayDescription'],
            ];
        });

        return $days;
    }

    private function getImages(array $links): array
    {
        return array_map(function (string $link) {
            return CFile::MakeFileArray($link);
        }, $links);
    }

//$data['countries'] - COUNTRY
//$data['tours'] -
//$data['coords'] -
}