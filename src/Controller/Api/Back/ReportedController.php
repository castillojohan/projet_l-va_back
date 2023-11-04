<?php

namespace App\Controller\Api\Back;

use App\Entity\Reported;
use App\Repository\ReportedRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportedController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Request the list of reporteds.
     *
     * This method retrieves a paginated list of reporteds and returns it as JSON data.
     *
     * @param int                $id                 The page number.
     * @param ReportedRepository $reportedRepository The repository for reporteds.
     * @param PaginatorService   $paginator          The paginator service.
     *
     * @return JsonResponse A JSON response containing the paginated reporteds data.
     *
     * @Route("/api/back/reporteds/{id<\d+>}", name="app_api_back_reporteds", methods="GET")
     */
    public function reportedsList($id, ReportedRepository $reportedRepository, PaginatorService $paginator): JsonResponse
    {
        $data = $paginator->buildPagination($id, $reportedRepository, "/api/back/reporteds/");

        if ($id > count($data['paginationLinks'])) {
            return $this->json(['error' => 'Resources not Found 404'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ['data' => $data],
            Response::HTTP_OK,
            [],
            ['groups' => 'reporteds_get_collection']
        );
    }


    /**
     * Request the data of a reported by its ID.
     *
     * This method retrieves the data of a reported specified by its ID and returns it as JSON data.
     *
     * @param Reported $reported The reported entity.
     *
     * @return JsonResponse A JSON response containing the reported data.
     *
     * @Route("/api/back/reporteds/{id<\d+>}/view", name="app_api_back_get_reported", methods="GET")
     */
    public function readReported(Reported $reported = null): JsonResponse
    {
        if ($reported === null) {
            throw $this->createNotFoundException('Reported not found');
        }

        return $this->json(
            ['reported' => $reported],
            Response::HTTP_OK,
            [],
            ['groups' => ['reporteds_get_collection', 'reported_get_item']]
        );
    }


    /**
     * Delete a reported by its ID.
     *
     * This method deletes a reported specified by its ID and returns a JSON response indicating the status.
     *
     * @param Reported $reported The reported entity.
     *
     * @return JsonResponse A JSON response indicating the status of the reported deletion.
     *
     * @Route("/api/back/reporteds/{id<\d+>}/delete", name="app_api_back_delete_reported", methods="DELETE")
     */
    public function deleteReported(Reported $reported): JsonResponse
    {
        if ($reported === null) {
            throw $this->createNotFoundException('404 Reported not found');
        }

        $this->entityManager->remove($reported);
        $this->entityManager->flush();

        return $this->json(
            ['status' => 'Reported deleted'],
            Response::HTTP_ACCEPTED
        );
    }
}
