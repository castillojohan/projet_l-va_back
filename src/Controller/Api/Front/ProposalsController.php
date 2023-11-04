<?php

namespace App\Controller\Api\Front;

use App\Entity\Proposal;
use App\Form\ProposalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProposalsController extends AbstractController
{
    private $serializer;
    private $entityManager;
    private $validator;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    
    /**
     * Create a user's proposal.
     *
     * This method allows a user to create a proposal by submitting a JSON request. It deserializes the JSON content into a `Proposal` entity, assigns the user to the proposal, validates it, and then persists it in the database.
     *
     * @param Request $request The HTTP request containing the JSON content of the proposal.
     *
     * @return JsonResponse A JSON response indicating the success of the proposal creation or validation errors.
     *
     * @Route("/api/front/proposals/add", name="app_api_front_proposals", methods="POST")
     */
    public function addProposal(Request $request): JsonResponse
    {
        $jsonContent = json_decode($request->getContent(), true);
        $proposal = new Proposal();

        $form = $this->createForm(ProposalType::class, $proposal);
        
        $form->submit(array_merge($jsonContent));
        
        $errors = $this->validator->validate($form);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json(
                ['errors' => $errorMessages],
                Response::HTTP_BAD_REQUEST
            );
        }

        $proposal->setUser($this->getUser());
        $this->entityManager->persist($proposal);
        $this->entityManager->flush();

        return $this->json(
            ['proposal' => $proposal],
            Response::HTTP_CREATED,
            [],
            ['groups' => 'proposals_get_collection']
        );
    }
}
