<?php

namespace App\Controller\Api\Back;

use App\Entity\Proposal;
use App\Repository\ProposalRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProposalsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Request the list of proposals.
     *
     * This method retrieves a paginated list of proposals and returns it as JSON data.
     *
     * @param int                $id                 The page number.
     * @param ProposalRepository $proposalRepository The repository for proposals.
     * @param PaginatorService   $paginator          The paginator service.
     *
     * @return JsonResponse A JSON response containing the paginated proposals data.
     *
     * @Route("/api/back/proposals/{id<\d+>}", name="app_api_back_proposals", methods="GET")
     */
    public function listProposals($id, ProposalRepository $proposalRepository, PaginatorService $paginator): JsonResponse
    {
        $data = $paginator->buildPagination($id, $proposalRepository, '/api/back/proposals/');

        if ($id > count($data['paginationLinks'])) {
            return $this->json(
                ['error' => 'Ressources not Found 404'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            ['data' => $data],
            Response::HTTP_OK,
            [],
            ['groups' => 'proposals_get_collection']
        );
    }


    /**
     * Request the data of a proposal by its ID.
     *
     * This method retrieves the data of a proposal specified by its ID and returns it as JSON data.
     *
     * @param Proposal $proposal The proposal entity.
     *
     * @return JsonResponse A JSON response containing the proposal data.
     *
     * @Route("/api/back/proposals/{id<\d+>}/view", name="app_api_back_get_proposal", methods="GET")
     */
    public function readProposal(Proposal $proposal = null): JsonResponse
    {
        if ($proposal === null) {
            throw $this->createNotFoundException('404 Proposal not found');
        }

        return $this->json(
            ['proposal' => $proposal],
            Response::HTTP_OK,
            [],
            ['groups' => 'proposals_get_collection']
        );
    }


    /**
     * Delete a proposal by its ID.
     *
     * This method deletes a proposal specified by its ID and returns a JSON response indicating the status.
     *
     * @param Proposal $proposal The proposal entity.
     *
     * @return JsonResponse A JSON response indicating the status of the proposal deletion.
     *
     * @Route("/api/back/proposals/{id<\d+>}/delete", name="app_api_back_proposal_delete", methods={"DELETE"})
     */
    public function deleteProposal(Proposal $proposal): JsonResponse
    {
        if ($proposal === null) {
            throw $this->createNotFoundException('404 Proposal not found');
        }

        $this->entityManager->remove($proposal);
        $this->entityManager->flush();

        return $this->json(
            ['status' => 'Proposal deleted'],
            Response::HTTP_ACCEPTED
        );
    }
}
