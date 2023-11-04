<?php

namespace App\Controller\Api\Back;

use App\Repository\ReportedRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends AbstractController
{
    /**
     * Search for reported and user entities.
     *
     * This method searches for reported and user entities based on the provided search term and returns the results as JSON data.
     *
     * @param Request            $request           The HTTP request.
     * @param ReportedRepository $reportedRepository The repository for reported entities.
     * @param UserRepository      $userRepository      The repository for user entities.
     *
     * @return Response A JSON response containing the search results.
     *
     * @Route("/api/back/search", name="app_api_back_search", methods="POST")
     */
    public function search(Request $request, ReportedRepository $reportedRepository, UserRepository $userRepository): JsonResponse
    {
        $searchTerm = json_decode($request->getContent());

        $reportedResults = $reportedRepository->findByReportedPseudo($searchTerm->search);
        $userResults = $userRepository->findByUserPseudo($searchTerm->search);

        $results = [
            'reported' => $reportedResults,
            'user' => $userResults,
        ];

        return $this->json(
            $results,
            Response::HTTP_OK,
            [],
            ['groups' => ['reporteds_get_collection', 'users_get_collection']]
        );
    }
}
