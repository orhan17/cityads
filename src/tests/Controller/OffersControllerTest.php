<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offer;
use App\Entity\Geo;

class OffersControllerTest extends WebTestCase
{
    public function testOffersNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/offers/ZZZZ');

        // Ожидаем 404
        $this->assertResponseStatusCodeSame(404);
    }
}
