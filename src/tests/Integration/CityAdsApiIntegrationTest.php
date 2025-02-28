<?php

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Тест, который реально стучится к CityAds API.
 * Осторожно: требует доступ к интернету и валидный endpoint.
 */
class CityAdsApiIntegrationTest extends TestCase
{
    public function testFetchOffersFromCityAds()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://cityads.com/api/rest/webmaster/v2/offers/list', [
            'query' => [
                'page' => 1,
                'perpage' => 5,
            ],
        ]);


        $this->assertSame(200, $response->getStatusCode(), 'Ожидаем 200 при удачном запросе');

        $data = $response->toArray();
        $this->assertArrayHasKey('offers', $data, 'Ожидаем ключ "offers" в ответе');

        $this->assertIsArray($data['offers'], 'offers должен быть массивом');
        if (count($data['offers']) > 0) {
            $firstOffer = $data['offers'][0];
            $this->assertArrayHasKey('id', $firstOffer);
            $this->assertArrayHasKey('name', $firstOffer);
        }
    }
}
