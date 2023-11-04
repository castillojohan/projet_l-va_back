<?php

namespace App\Controller\Api\Back;

use App\Entity\CaseFolder;
use App\Repository\CaseFolderRepository;
use App\Service\AppMailer;
use App\Service\PaginatorService;
use App\Service\PictureService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CaseFoldersController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Request the list of case folders for a given page.
     *
     * @param int               $id            The page number.
     * @param CaseFolderRepository $casesRepository The repository for case folders.
     * @param PaginatorService  $paginator     The paginator service.
     *
     * @return JsonResponse A JSON response containing the paginated case folders data.
     *
     * @Route("/api/back/cases/{id<\d+>}", name="app_api_back_casefolders", methods="GET")
     */
    public function casesList($id, CaseFolderRepository $casesRepository, PaginatorService $paginator): JsonResponse
    {
        $data = $paginator->buildPagination($id, $casesRepository, "/api/back/cases/");

        if ($id > count($data['paginationLinks'])) {
            return $this->json(['error' => 'Ressources not Found 404'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ["data" => $data],
            Response::HTTP_OK,
            [],
            ['groups' => ['cases_get_collections', 'case_get_screenshots']]
        );
    }


    /**
     * Request the data of a case folder by its ID.
     *
     * @param CaseFolder $case The case folder entity.
     *
     * @return JsonResponse A JSON response containing the case folder data.
     *
     * @Route("/api/back/cases/{id<\d+>}/view", name="app_api_back_get_case", methods="GET")
     */
    public function readCase(CaseFolder $case = null, UrlGeneratorInterface $router): JsonResponse
    {
        if ($case === null) {
            throw $this->createNotFoundException('Case not found');
        }

        return $this->json(
            [
            'cases' => $case,
            'link' => $router->generate('app_api_back_get_case', ['id' => $case->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['cases_get_collections', 'case_get_item', 'case_get_screenshots']]
        );
    }


    /**
     * Move a case folder to an Ongoing state.
     *
     * @param CaseFolder $case      The case folder entity.
     * @param AppMailer  $appMailer The AppMailer service for sending emails.
     *
     * @return JsonResponse A JSON response containing the updated case folder data.
     *
     * @Route("/api/back/cases/{id<\d+>}/ongoing", name="app_api_back_ongoing_case", methods="GET")
     */
    public function updateOngoingCase(CaseFolder $case = null, AppMailer $appMailer): JsonResponse
    {
        if ($case === null) {
            throw $this->createNotFoundException('No case found');
        }

        $case->setStatus(['ONGOING']);
        $case->setUpdatedAt(new DateTime());

        $this->entityManager->persist($case);
        $this->entityManager->flush();

        $user = $case->getUser();
        $appMailer->ongoingFolder($case, $user);

        return $this->json(
            ["case" => $case],
            Response::HTTP_CREATED,
            [],
            ["groups" => ["cases_get_collection", "case_get_item"]]
        );
    }


    /**
     * Move a case folder to a Processing state.
     *
     * @param CaseFolder $case      The case folder entity.
     * @param AppMailer  $appMailer The AppMailer service for sending emails.
     *
     * @return JsonResponse A JSON response containing the updated case folder data.
     *
     * @Route("/api/back/cases/{id<\d+>}/processed", name="app_api_back_processed_case", methods="GET")
     */
    public function updateProcessedCase(CaseFolder $case = null, AppMailer $appMailer): JsonResponse
    {
        if ($case === null) {
            throw $this->createNotFoundException('No case to delete');
        }

        $case->setStatus(['PROCESSED']);
        $case->setUpdatedAt(new DateTime());

        $this->entityManager->persist($case);
        $this->entityManager->flush();

        $user = $case->getUser();
        $appMailer->processedFolder($case, $user);

        return $this->json(
            ["case" => $case],
            Response::HTTP_CREATED,
            [],
            ["groups" => ["cases_get_collection", "case_get_item"]]
        );
    }


    /**
     * Delete a case folder by its ID.
     *
     * @param CaseFolder $case The case folder entity.
     *
     * @return JsonResponse An empty JSON response indicating a successful deletion.
     *
     * @Route("/api/back/cases/{id<\d+>}/delete", name="app_api_back_delete_case", methods="DELETE")
     */
    public function deleteCase(CaseFolder $case = null, PictureService $pictureService): JsonResponse
    {
        if ($case === null) {
            throw $this->createNotFoundException('Case Not Found');
        }

        $screenshots = $case->getScreenshots();
        
        if ($screenshots) {
            foreach ($screenshots as $screenshot) {
                $pictureService->delete($screenshot->getName());
            }
        }

        $this->entityManager->remove($case);
        $this->entityManager->flush();

        return $this->json(
            [],
            Response::HTTP_ACCEPTED,
            [],
            []
        );
    }
}
