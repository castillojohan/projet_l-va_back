<?php

namespace App\Controller\Api;

use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Form\MessagesType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessagesController extends AbstractController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * Send a message to a recipient.
     *
     * @param Request $request The HTTP request containing the message data.
     * @param UserRepository $userRepository The user repository.
     *
     * @return JsonResponse A JSON response indicating the success of the message sending or validation errors.
     *
     * @Route("/api/sendMessage", name="app_api_send_messages", methods={"GET", "POST"})
     */
    public function send(Request $request, UserRepository $userRepository): JsonResponse
    {
        $levaMessaging = $userRepository->findOneBy(['pseudo' => 'LÉVA']);

        if ($request->isMethod('POST')) {
            $message = new Message();
            $user = $this->getUser();

            $data = json_decode($request->getContent(), true);

            if ($user->getRoles() === ["ROLE_ADMIN"] || $user->getRoles() === ["ROLE_MANAGER"]) {
                $message->setSender($levaMessaging);
            } else {
                $message->setSender($user);
                $message->setRecipient($levaMessaging);
            }

            $form = $this->createForm(MessagesType::class, $message);
            $form->submit(array_merge($data), false);

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

            if (count($errors) < 1 && $form->isSubmitted()) {
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            } else {
                return $this->json(["error" => "An error has occured"], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['message' => 'Message sent']);
        }

        $userRole = $this->getUser()->getRoles();

        switch ($userRole) {
            case in_array("ROLE_CERTIFIED_USER", $userRole):
                $getRecipients = $levaMessaging;
                break;
            case ($userRole === ["ROLE_ADMIN"] || $userRole === ["ROLE_MANAGER"]):
                $getRecipients = $userRepository->findAll();
                break;
        }

        return $this->json(
            ["user" => $getRecipients],
            Response::HTTP_CREATED,
            [],
            ['groups' => 'messages_get_recipient']
        );
    }

    /**
     * Get received messages for the currently logged-in user.
     *
     * @param MessageRepository $messageRepository The message repository.
     *
     * @return JsonResponse A JSON response containing the user's received messages.
     *
     * @Route("/api/receiveMessage", name="app_api_receive_messages", methods="GET")
     */
    public function receive(Request $request, MessageRepository $messageRepository, UserRepository $userRepository): JsonResponse
    {
        $user = $this->getUser();

        $messages = $messageRepository->findBy(['recipient' => $user]);

        if ($user->getRoles() === ["ROLE_ADMIN"] || $user->getRoles() === ["ROLE_MANAGER"]) {
            $user = $userRepository->findOneBy(['pseudo' => 'LÉVA']);
            $messages = $messageRepository->findBy(['recipient' => $user]);
        }

        return $this->json(
            ['messages' => $messages],
            Response::HTTP_OK,
            [],
            ['groups' => 'messages_get_collection']
        );
    }

    /**
     * Read a message and update its reading status.
     *
     * This method reads a message, sets its reading status to true,
     * and returns a JSON response with the message data.
     *
     * @param Message $message The message to be read.
     *
     * @Route("/api/messages/{id<\d+>}", name="app_api_get_message", methods="GET")
     */
    public function readMessage(Message $message = null): JsonResponse
    {
        if ($message === null) {
            throw $this->createNotFoundException('Message not found');
        }
        $this->denyAccessUnlessGranted('view', $message, 'This message is not your concern');

        $message->setReadingStatus(true);
        $this->entityManager->flush();

        return $this->json(
            ['message' => $message],
            Response::HTTP_OK,
            [],
            ['groups' => 'messages_get_collection']
        );
    }
}
