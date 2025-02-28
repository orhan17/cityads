<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Geo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер для методов /offers/{geo} и /geo-stats
 */
class OffersController extends AbstractController
{
    /**
     * GET /offers/{geo}
     * - Параметры пагинации: ?page=1&limit=5 (макс. 20)
     * - Сортируем офферы по убыванию rating
     * - Если нет офферов для такого GEO – возвращаем 404
     */
    #[Route('/offers/{geo}', name: 'offers_by_geo', methods: ['GET'])]
    public function getOffersByGeo(
        string $geo,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 5);
        if ($limit > 20) {
            $limit = 20;
        }

        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from(Offer::class, 'o')
            ->join('o.geos', 'g')
            ->where('g.code = :geo')
            ->setParameter('geo', $geo)
            ->orderBy('o.rating', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $offers = $qb->getQuery()->getResult();

        if (!$offers) {
            return $this->json([
                'error' => "Нет офферов для GEO: {$geo} или GEO не поддерживается."
            ], 404);
        }

        $qbCount = $em->createQueryBuilder();
        $qbCount->select('COUNT(o.id)')
            ->from(Offer::class, 'o')
            ->join('o.geos', 'g')
            ->where('g.code = :geo')
            ->setParameter('geo', $geo);
        $total = (int) $qbCount->getQuery()->getSingleScalarResult();

        $data = [];
        foreach ($offers as $offer) {
            $data[] = [
                'id' => $offer->getId(),
                'name' => $offer->getName(),
                'currencyName' => $offer->getCurrencyName(),
                'approvalTime' => $offer->getApprovalTime(),
                'paymentTime' => $offer->getPaymentTime(),
                'siteUrl' => $offer->getSiteUrl(),
                'logo' => $offer->getLogo(),
                'rating' => $offer->getRating(),
            ];
        }

        return $this->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * GET /geo-stats
     * - Возвращает список всех GEO с количеством связанных офферов
     */
    #[Route('/geo-stats', name: 'geo_stats', methods: ['GET'])]
    public function getGeoStats(EntityManagerInterface $em): JsonResponse
    {
        $dql = "
            SELECT g.code AS code, g.name AS name, COUNT(o.id) AS offersCount
            FROM App\Entity\Geo g
            LEFT JOIN g.offers o
            GROUP BY g.id
        ";
        $result = $em->createQuery($dql)->getResult();

        return $this->json($result);
    }
}
