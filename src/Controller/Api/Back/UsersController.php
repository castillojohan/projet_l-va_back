<?php

namespace App\Controller\Api\Back;

use App\Entity\User;
use App\Form\UsersType;
use App\Service\AppMailer;
use App\Service\PictureService;
use App\Service\PaginatorService;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use App\Repository\ProposalRepository;
use App\Repository\CaseFolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ScreenshotsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Request the data of the users list.
     *
     * This method retrieves a paginated list of users and returns it as JSON data.
     *
     * @param int             $id           The page number.
     * @param UserRepository  $userRepository The repository for users.
     * @param PaginatorService $paginator     The paginator service.
     *
     * @return JsonResponse A JSON response containing the paginated users data.
     *
     * @Route("/api/back/users/{id<\d+>}", name="app_api_back_users", methods="GET")
     */
    public function usersList($id, UserRepository $userRepository, PaginatorService $paginator): JsonResponse
    {
        $data = $paginator->buildPagination($id, $userRepository, "/api/back/users/");

        if ($id > count($data['paginationLinks'])) {
            throw $this->createNotFoundException('Not Found');
        }

        return $this->json(
            ['data' => $data],
            Response::HTTP_OK,
            [],
            ['groups' => 'users_get_collection']
        );
    }


    /**
     * Request the data of a user by their ID.
     *
     * This method retrieves the data of a user specified by their ID and returns it as JSON data.
     *
     * @param User $user The user entity.
     *
     * @return JsonResponse A JSON response containing the user data.
     *
     * @Route("/api/back/users/{id<\d+>}/view", name="app_api_back_get_users", methods="GET")
     */
    public function readUser(User $user = null): JsonResponse
    {
        if ($user === null) {
            throw $this->createNotFoundException('User doesn\'t exist');
        }

        return $this->json(
            ['user' => $user],
            Response::HTTP_OK,
            [],
            ['groups' => ['users_get_collection', 'user_get_item']]
        );
    }


    /**
     * Adds a new user from the backoffice.
     *
     * This method allows administrators to add a new user by submitting a JSON request. It deserializes the JSON content into a `User` entity, hashes the user's password, validates the user data, and then persists the user in the database.
     *
     * @param Request $request The HTTP request containing user registration data.
     * @param ValidatorInterface $validator The validator service for form validation.
     * @param SerializerInterface $serializer The serializer service for deserializing JSON content.
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service.
     *
     * @return JsonResponse A JSON response indicating the success of user creation or validation errors.
     *
     * @Route("/api/back/users/add", name="app_api_back_add_users", methods="POST")
     */
    public function addUser(Request $request, ValidatorInterface $validator, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('addAndModify', $this->getUser(), "Acces rejected, you are not an admin.");

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $plainPassword = $user->getPassword();
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $errors = $validator->validate($user);

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

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(
            ["user" => $user],
            Response::HTTP_CREATED,
            [],
            ["groups" => "users_get_collection"]
        );
    }


    /**
     * Update a user by their ID.
     *
     * This method updates a user specified by their ID with the provided data and returns the updated user as JSON data.
     *
     * @param Request            $request   The HTTP request.
     * @param User               $user      The user entity.
     * @param ValidatorInterface $validator The validator service.
     *
     * @return JsonResponse A JSON response containing the updated user data or validation errors.
     *
     * @Route("/api/back/users/{id<\d+>}/update", name="app_api_back_users_update", methods="PATCH")
     */
    public function updateUser(Request $request, User $user = null, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('addAndModify', $this->getUser(), "Acces rejected, you are not an admin.");

        if ($user === null) {
            throw $this->createNotFoundException('User doesn\'t exist');
        }

        $json = json_decode($request->getContent(), true);

        $form = $this->createForm(UsersType::class, $user);
        $form->submit(array_merge($json), false);

        $errors = $validator->validate($form);

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

        if (count($errors) < 1 && $form->isSubmitted()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            return $this->json(["errors" => "An error has occured, the modification has not been validated"], Response::HTTP_NOT_MODIFIED);
        }

        return $this->json(
            ["user" => $user],
            Response::HTTP_CREATED,
            [],
            ['groups' => 'users_get_collection', 'user_get_item']
        );
    }


    /**
     * Change a user's role to "ROLE_CERTIFIED".
     *
     * @Route("/api/back/users/{id<\d+>}/certify", name="app_api_back_certify_user", methods="GET")
     */
    public function updateCertifyUser(User $user = null, AppMailer $appMailer): JsonResponse
    {
        if ($user === null) {
            throw $this->createNotFoundException('User doesn\'t exist');
        }

        $userRoles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $userRoles) || in_array('ROLE_MANAGER', $userRoles)) {
            return $this->json(["error" => "Permission denied"], Response::HTTP_FORBIDDEN);
        }

        $user->setRole(['ROLE_CERTIFIED_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $appMailer->certifiedUser($user);

        return $this->json(
            ["user" => $user],
            Response::HTTP_CREATED,
            [],
            ["groups" => ["users_get_collection", "user_get_item"]]
        );
    }


    /**
     * Change a user's role to "ROLE_DESACTIVATED".
     *
     * @Route("/api/back/users/{id<\d+>}/desactivated", name="app_api_back_desactivated_user", methods="GET")
     */
    public function updateDesactivedUser(User $user = null, AppMailer $appMailer): JsonResponse
    {
        if ($user === null) {
            throw $this->createNotFoundException('User doesn\'t exist');
        }

        $userRoles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $userRoles) || in_array('ROLE_MANAGER', $userRoles)) {
            return $this->json(["error" => "Permission denied"], Response::HTTP_FORBIDDEN);
        }

        $user->setRole(['ROLE_DESACTIVATED_USER'])
             ->setPseudo('desactivated');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $appMailer->desactivatedUser($user);

        return $this->json(
            ["user" => $user],
            Response::HTTP_CREATED,
            [],
            ["groups" => ["users_get_collection", "user_get_item"]]
        );
    }


    /**
     * Deactivate a user by their ID.
     *
     * This method deactivates a user specified by their ID and deletes associated cases, screenshots, messages, and proposals.
     *
     * @param User $user The user entity to deactivate.
     * @param CaseFolderRepository $caseFolderRepository The repository for case folders.
     * @param ScreenshotsRepository $screenshotRepository The repository for screenshots.
     * @param MessageRepository $messageRepository The repository for messages.
     * @param ProposalRepository $proposalRepository The repository for proposals.
     * @param PictureService $pictureService The service for managing pictures.
     *
     * @return JsonResponse A JSON response indicating the status of the user deactivation and associated data deletion.
     *
     * @Route("/api/back/users/{id<\d+>}/delete", name="app_api_back_users_delete", methods="DELETE")
     */
    public function deleteUser(User $user = null, CaseFolderRepository $caseFolderRepository, ScreenshotsRepository $screenshotRepository, MessageRepository $messageRepository, ProposalRepository $proposalRepository, PictureService $pictureService): JsonResponse 
    {
        if ($user === null) {
            throw $this->createNotFoundException('User doesn\'t exist');
        }

        $caseFolders = $caseFolderRepository->findBy(['user' => $user]);
        foreach ($caseFolders as $caseFolder) {
            $screenshots = $screenshotRepository->findBy(['caseFolder' => $caseFolder]);
            foreach ($screenshots as $screenshot) {
                $pictureService->delete($screenshot->getName());
            }
            $this->entityManager->remove($caseFolder);
        }

        
        $messages = $messageRepository->findBy(['sender' => $user]);
        foreach ($messages as $message) {
            $this->entityManager->remove($message);
        }

        
        $proposals = $proposalRepository->findBy(['user' => $user]);
        foreach ($proposals as $proposal) {
            $this->entityManager->remove($proposal);
        }

        
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(
            ['status' => 'User and associated data deleted'],
            Response::HTTP_ACCEPTED
        );
    }
}
