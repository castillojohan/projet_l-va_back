<?php

namespace App\Controller\Api\Back;

use App\Repository\UserRepository;
use App\Repository\ReportedRepository;
use App\Repository\CaseFolderRepository;
use App\Repository\PlatformRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BackOfficeController extends AbstractController
{
    /**
     * Dispatches all data for the backoffice homepage.
     *
     * This method is responsible for retrieving various statistics and data
     * needed for the backoffice homepage, such as case statistics, platform statistics,
     * user statistics, and the latest cases.
     *
     * @param CaseFolderRepository    $caseFolderRepository    The repository for case folders.
     * @param ReportedRepository      $reportedRepository      The repository for reported entities.
     * @param UserRepository          $userRepository            The repository for user entities.
     * @param PlatformRepository      $platformRepository        The repository for platform entities.
     *
     * @return JsonResponse A JSON response containing the collected data.
     *
     * @Route("/api/back/home", name="app_api_back_home", methods="GET")
     */
    public function home(CaseFolderRepository $caseFolderRepository, ReportedRepository $reportedRepository, UserRepository $userRepository, PlatformRepository $platformRepository): JsonResponse {
         
        $totalCases = count($caseFolderRepository->findAll());

        $casesProcessed = count($caseFolderRepository->findProcessedCases());
         
        $casesOngoing = count($caseFolderRepository->findOngoingCases());

        $lastCases = $caseFolderRepository->findBy([], ['id' => 'DESC'], 10);

        $platforms = $platformRepository->findAll();
        $statPlatform = [];

        foreach ($platforms as $platform) {
            $platformName = $platform->getName();
            $casesByPlatform = count($platform->getCaseFolders());
            $statPlatform[] = ["name" => $platformName, "reportedNb" => $casesByPlatform];
        }

        $users = $userRepository->findAll();
        $usersNb = count($users);

        $reporteds = $reportedRepository->findAll();
        $totalReported = count($reporteds);

        return $this->json(
            ['data' => [
                "casesNb" => $totalCases,
                "casesProcessed" => $casesProcessed,
                "casesOngoing" => $casesOngoing,
                "reportedsNb" => $totalReported,
                "usersNb" => $usersNb,
                "statPlatform" => $statPlatform,
                "lastCases" => $lastCases
            ]],
            Response::HTTP_OK,
            [],
            ['groups' => ['backoffice_get_collection', 'cases_get_links']]
        );
    }
}
