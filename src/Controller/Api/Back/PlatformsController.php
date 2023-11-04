<?php

namespace App\Controller\Api\Back;

use App\Entity\Platform;
use App\Form\PlatformsType;
use App\Repository\PlatformRepository;
use App\Service\PaginatorService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlatformsController extends AbstractController
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
     * Request the list of all platforms.
     *
     * @param int                $id                 The page number.
     * @param PlatformRepository $platformRepository The repository for platforms.
     * @param PaginatorService   $paginator          The paginator service.
     *
     * @return JsonResponse A JSON response containing the paginated platforms data.
     *
     * @Route("/api/back/platforms/{id<\d+>}", name="app_api_back_platforms", methods={"GET"})
     */
    public function platformsList($id, PlatformRepository $platformRepository, PaginatorService $paginator): JsonResponse
    {
        $data = $paginator->buildPagination($id, $platformRepository, "/api/back/platforms/");

        if ($id > count($data['paginationLinks'])) {
            return $this->json(['error' => 'Ressources not Found 404'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ['data' => $data],
            Response::HTTP_OK,
            [],
            ['groups' => 'platforms_get_collection']
        );
    }


    /**
     * Request the data of a platform by its platformId.
     *
     * @param Platform $platform The platform entity.
     *
     * @return JsonResponse A JSON response containing the platform data.
     *
     * @Route("/api/back/platforms/{id<\d+>/view}", name="app_api_back_get_platform", methods={"GET"})
     */
    public function readPlatform(Platform $platform = null): JsonResponse
    {
        if ($platform === null) {
            throw $this->createNotFoundException('Platform not found');
        }

        return $this->json(
            ['result' => $platform],
            Response::HTTP_OK,
            [],
            ['groups' => ['platforms_get_collection', 'platform_get_item']]
        );
    }


    /**
     * Create a platform.
     *
     * @param Request $request The HTTP request.
     *
     * @return JsonResponse A JSON response containing the created platform data or validation errors.
     *
     * @Route("/api/back/platforms/add", name="app_api_back_add_platform", methods={"POST"})
     */
    public function addPlatform(Request $request): JsonResponse
    {
        $jsonContent = $request->getContent();

        $platform = $this->serializer->deserialize($jsonContent, Platform::class, 'json');

        $errors = $this->validator->validate($platform);

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

        $this->entityManager->persist($platform);
        $this->entityManager->flush();

        return $this->json(
            $platform,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['platforms_get_collection', 'platform_get_item']]
        );
    }


    /**
     * Edit a platform according to its ID.
     *
     * @param Request  $request  The HTTP request.
     * @param Platform $platform The platform entity.
     *
     * @return JsonResponse A JSON response containing the updated platform data or validation errors.
     *
     * @Route("/api/back/platforms/{id<\d+>}/update", name="app_api_back_update_platform", methods={"PATCH"})
     */
    public function updatePlatform(Request $request, Platform $platform = null): JsonResponse
    {
        if ($platform === null) {
            throw $this->createNotFoundException('Platform not found');
        }

        $json = json_decode($request->getContent(), true);

        $form = $this->createForm(PlatformsType::class, $platform);

        $form->submit(array_merge($json, $request->request->all()));

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

        $platform->setUpdatedAt(new DateTime());

        $this->entityManager->persist($platform, true);
        $this->entityManager->flush();

        return $this->json(
            $platform,
            Response::HTTP_OK,
            [],
            ['groups' => ['platforms_get_collection', 'platform_get_item']]
        );
    }


    /**
     * Delete a platform according to its ID.
     *
     * @param Platform $platform The platform entity.
     *
     * @return JsonResponse A JSON response indicating the status of platform deletion.
     *
     * @Route("/api/back/platforms/{id<\d+>}/delete", name="app_api_back_delete_platform", methods={"DELETE"})
     */
    public function deletePlatform(Platform $platform): JsonResponse
    {
        if ($platform === null) {
            throw $this->createNotFoundException('Platform not found');
        }
        //! Relationship make some troubles on delete, persistence, deactivate mode
        $this->entityManager->remove($platform);
        $this->entityManager->flush();

        return $this->json(
            ['status' => 'platform deleted'],
            Response::HTTP_ACCEPTED
        );
    }
}
