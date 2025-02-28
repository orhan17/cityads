<?php

namespace App\Controller;

use App\Service\CityAdsSyncService;
use App\Entity\SyncState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для POST /sync-offers
 */
class SyncController extends AbstractController
{
    #[Route('/sync-offers', name: 'sync_offers', methods: ['POST'])]
    public function syncOffers(
        EntityManagerInterface $em,
        CityAdsSyncService $cityAdsSyncService
    ): JsonResponse {

        $syncState = $em->getRepository(SyncState::class)->find(1);

        if(is_null($syncState)) {
            $syncState = new SyncState();
            $syncState->setSyncInProgress(false);
            $em->persist($syncState);
            $em->flush();
        }

        if (!$syncState) {
            $syncState = new SyncState();
            $em->persist($syncState);
            $em->flush();
        }

        if ($syncState->isSyncInProgress()) {
            return $this->json([
                'error' => 'Синхронизация уже выполняется, дождитесь завершения.'
            ], 409);
        }

        $syncState->setSyncInProgress(true);
        $em->flush();

        try {
            $cityAdsSyncService->syncOffers();

            $syncState->setSyncInProgress(false);
            $em->flush();
        } catch (\Throwable $e) {
            $syncState->setSyncInProgress(false);
            $em->flush();
            throw $e;
        }

        return $this->json(['message' => 'Синхронизация завершена']);
    }
}
