<?php

namespace App\Controller\Api\Front;

use App\Entity\User;
use App\Service\AppMailer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordResetController extends AbstractController
{
    private $entityManager;
    private $tokenGenerator;

    public function __construct(TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->entityManager = $entityManager;
    }


    /**
     * Request a password reset for a user.
     *
     * This method allows a user to request a password reset by providing their email. It generates a unique token and sends a password reset link to the user's email address.
     *
     * @param Request       $request      The HTTP request.
     * @param UserRepository $userRepository The repository for user data.
     * @param AppMailer     $appMailer    The mailer service for sending email notifications.
     *
     * @return JsonResponse A JSON response indicating the success of the request or any potential errors.
     *
     * @Route("/api/front/resetPassword", name="app_api_front_resetPassword", methods="POST")
     */
    public function requestPasswordRequest(Request $request, UserRepository $userRepository, AppMailer $appMailer): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $userEmail = $requestData['email'];

        $user = $userRepository->findOneBy(['email' => $userEmail]);

        if (!$user) {
            throw $this->createNotFoundException('404 email not found');
            
            return $this->json(
                ['errors' => 'This email could not be found'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->setForgotPasswordToken($this->tokenGenerator->generateToken())
            ->setForgotPasswordTokenCreatedAt(new \DateTimeImmutable('now'))
            ->setForgotPasswordTokenExpireAfter(new \DateTimeImmutable('+10 minutes'));
        
        $this->entityManager->flush($user);
        $this->entityManager->persist($user);

        $appMailer->recoveryLink($user);

        return $this->json(
            ['user' => $user],
            Response::HTTP_OK,
            [],
            ['groups' => 'user_get_item']
        );
    }


    /**
     * Set a new password for a user.
     *
     * This method allows a user to set a new password after receiving a password reset token. It validates the token and sets the new password for the user.
     *
     * @param Request                  $request        The HTTP request.
     * @param UserRepository            $userRepository  The repository for user data.
     * @param UserPasswordHasherInterface $passwordHasher The password hasher service.
     * @param AppMailer                $appMailer      The mailer service for sending email notifications.
     *
     * @return JsonResponse A JSON response indicating the success of the password reset or any potential errors.
     *
     * @Route("/api/front/newPassword", name="app_api_front_newPassword", methods="POST")
     */
    public function newPassword(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, AppMailer $appMailer): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $password = $requestData['password'];
        
        $tokenFromRequest = $requestData['token'];
    
        $user = $userRepository->findOneBy(['forgotPasswordToken' => $tokenFromRequest]);

        if (!$user) {
            return $this->json(
                ['error' => 'Jeton de réinitialisation invalide'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $currentDateTime = new \DateTimeImmutable();
        $tokenExpireTime = $user->getForgotPasswordTokenExpireAfter();
        
        if ($currentDateTime > $tokenExpireTime) {
            return $this->json(
                ['error' => 'Jeton de réinitialisation expiré'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $password);

        $user->setPassword($hashedPassword)
            ->setForgotPasswordToken(null)
            ->setForgotPasswordTokenCreatedAt(null)
            ->setForgotPasswordTokenExpireAfter(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $appMailer->passwordReset($user);
    
        return $this->json(
            ['message' => 'Mot de passe réinitialisé avec succès'],
            Response::HTTP_CREATED
        );
    }
}
