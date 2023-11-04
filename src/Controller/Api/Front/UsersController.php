<?php

namespace App\Controller\Api\Front;

use DateTime;
use App\Entity\User;
use App\Form\UsersType;
use App\Service\AppMailer;
use Doctrine\ORM\EntityManagerInterface;
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
     * Get data of the currently logged-in user.
     *
     * @return JsonResponse A JSON response containing the user's data.
     *
     * @Route("/api/front/connected", name="app_api_front_connected", methods="GET")
     */
    public function loggedIn(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['error' => 'Access forbidden'],
                Response::HTTP_FORBIDDEN,
                [],
                []
            );
        }

        return $this->json(
            ['user' => $user],
            Response::HTTP_OK,
            [],
            ['groups' => ['users_get_collection', 'user_get_role', 'case_get_item']]
        );
    }

    /**
     * Register a new user.
     *
     * @param Request $request The HTTP request containing user registration data.
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service.
     * @param AppMailer $appMailer The mailer service for sending account creation emails.
     *
     * @return JsonResponse A JSON response indicating the success of user registration or validation errors.
     *
     * @Route("/api/front/register/add", name="app_api_front_register", methods="POST")
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, AppMailer $appMailer): JsonResponse
    {
        $jsonContent = json_decode($request->getContent(), true);
        
        $user = new User();

        $form = $this->createForm(UsersType::class, $user);
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

        $plainPassword = $user->getPassword();
        $password = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $appMailer->createAccount($user);

        return $this->json(
            ['user' => $user],
            Response::HTTP_CREATED,
            [],
            ['groups' => 'user_get_item']
        );
    }

    /**
     * Get data of a user by their ID.
     *
     * @param User $user The user entity.
     *
     * @return JsonResponse A JSON response containing the user's data.
     *
     * @Route("/api/front/profils/{id<\d+>}", name="app_api_front_read_profil", methods="GET")
     */
    public function readProfil(User $user = null): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $user, "This ressources does not belong to you");
        return $this->json(
            ['user' => $user],
            Response::HTTP_OK,
            [],
            ['groups' => ['users_get_collection', 'user_get_item']]
        );
    }

    /**
     * Update a user's profile by their ID.
     *
     * @param User $user The user entity to be updated.
     * @param Request $request The HTTP request containing updated user data.
     * @param ValidatorInterface $validator The validator service for form validation.
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service.
     *
     * @return JsonResponse A JSON response indicating the success of the profile update or validation errors.
     *
     * @Route("/api/front/profils/{id<\d+>}/update", name="app_api_front_edit_profil", methods="PATCH")
     */
    public function updateProfil(User $user = null, Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $user, 'You can not modify a profil that does not belong to you');

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
            if (key_exists('password', $json)) {
                $plainPassword = $user->getPassword();
                $password = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($password);
            }

            $user->setUpdatedAt(new DateTime());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $this->json(
            ['user' => $user],
            Response::HTTP_CREATED,
            [],
            ['groups' => "users_get_collection"]
        );
    }
}
