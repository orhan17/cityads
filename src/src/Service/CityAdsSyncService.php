<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offer;
use App\Entity\Geo;

class CityAdsSyncService
{
    private HttpClientInterface $client;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->em = $em;
    }

    public function syncOffers(): void
    {
        $page = 1;
        $limit = 20;

        while (true) {
            $response = $this->client->request('GET', 'https://cityads.com/api/rest/webmaster/v2/offers/list', [
                'query' => [
                    'page' => $page,
                    'perpage' => $limit,
                ],
            ]);

            $data = $response->toArray();

            if (!isset($data['offers']) || count($data['offers']) === 0) {
                break;
            }

            $this->processOffers($data['offers']);

            $page++;
        }
    }

    private function processOffers(array $offersData): void
    {
        $batchSize = 20;
        $count = 0;

        $geoCache = [];

        foreach ($offersData as $offerItem) {
            if ($this->hasWrld($offerItem['geo'] ?? [])) {
                continue;
            }

            $externalId = $offerItem['id'] ?? null;
            if (!$externalId) {
                continue;
            }

            $offer = $this->em->getRepository(Offer::class)->findOneBy(['externalId' => $externalId]);

            if (!$offer) {
                $offer = new Offer();
                $offer->setExternalId($externalId);

                $this->em->persist($offer);
            }

            $offer->setName($offerItem['name'] ?? '');
            $offer->setCurrencyName($offerItem['offer_currency']['name'] ?? '');
            $offer->setApprovalTime((int) ($offerItem['approval_time'] ?? 0));
            $offer->setPaymentTime((int) ($offerItem['payment_time'] ?? 0));
            $offer->setSiteUrl($offerItem['site_url'] ?? null);
            $offer->setLogo($offerItem['logo'] ?? null);

            $ecpl = (float) ($offerItem['stat']['ecpl'] ?? 0);
            $rating = $this->calculateRating($ecpl, $offer->getApprovalTime(), $offer->getPaymentTime());
            $offer->setRating($rating);

            if (isset($offerItem['geo']) && is_array($offerItem['geo'])) {
                foreach ($offerItem['geo'] as $g) {
                    $geoCode = strtoupper(trim($g['code'] ?? ''));
                    if ($geoCode === 'WRLD') {
                        continue;
                    }
                    $geoName = trim($g['name'] ?? '');

                    if (!isset($geoCache[$geoCode])) {
                        $geoEntity = $this->em->getRepository(Geo::class)->findOneBy(['code' => $geoCode]);
                        if (!$geoEntity) {
                            $geoEntity = new Geo();
                            $geoEntity->setCode($geoCode);
                            $geoEntity->setName($geoName);

                            $this->em->persist($geoEntity);
                        } else {
                            if ($geoEntity->getName() !== $geoName) {
                                $geoEntity->setName($geoName);
                                $this->em->persist($geoEntity);
                            }
                        }
                        $geoCache[$geoCode] = $geoEntity;
                    }
                    $offer->addGeo($geoCache[$geoCode]);
                }
            }

            $this->em->persist($offer);

            $count++;
            if ($count % $batchSize === 0) {
                $this->em->flush();
                $this->em->clear();

                $geoCache = [];
            }
        }

        if ($count % $batchSize !== 0) {
            $this->em->flush();
            $this->em->clear();
        }
    }

    /**
     * Проверяет, есть ли среди гео оффера "Wrld"
     */
    private function hasWrld(array $geoList): bool
    {
        foreach ($geoList as $g) {
            $code = strtoupper(trim($g['code'] ?? ''));
            if ($code === 'WRLD') {
                return true;
            }
        }
        return false;
    }

    /**
     * Формула:
     *   rating = ecpl * coeff1 * coeff2
     *   coeff1 = 10 * (1 - approvalTime / 90)
     *   coeff2 = 100 * (1 - paymentTime / 90)
     *
     * Если coeff1 <= 0 или coeff2 <= 0 — считаемм их = 1
     */
    private function calculateRating(float $ecpl, int $approvalTime, int $paymentTime): float
    {
        $coeff1 = 10 * (1 - $approvalTime / 90);
        $coeff2 = 100 * (1 - $paymentTime / 90);

        if ($coeff1 <= 0) {
            $coeff1 = 1;
        }
        if ($coeff2 <= 0) {
            $coeff2 = 1;
        }

        return $ecpl * $coeff1 * $coeff2;
    }
}
