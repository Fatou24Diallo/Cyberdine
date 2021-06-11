<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\ParticipantRepository;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class ConversationController extends AbstractController
{

    const ATTRIBUTES_TO_SERIALIZE = ['id', 'content', 'createdAt', 'mine'];
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var MessageRepository
     */
    private $messageRepository;
    /**
     * @var ParticipantRepository
     */
    private $participantRepository;
    /**
     * @var PublisherInterface
     */
    private $publisher;


    public function __construct(UserRepository $userRepository,
                                EntityManagerInterface $entityManager,
                                MessageRepository $messageRepository,
                                ConversationRepository $conversationRepository,
                                ParticipantRepository $participantRepository,
                                PublisherInterface $publisher)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->participantRepository = $participantRepository;
        $this->publisher = $publisher;
    }

    /**
     * @Route("/conversations/{id}", name="newConversations", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, $id)
    {
        $otherUser = $request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($id);

        if (is_null($otherUser)) {
            throw new \Exception("The user was not found");
        }

        // cannot create a conversation with myself
        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception("That's deep but you cannot create a conversation with yourself");
        }

        // Check if conversation already exists
        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );

        if (count($conversation)) {
            throw new \Exception("The conversation already exists");
        }

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);


        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }


        return $this->json([
            'id' => $conversation->getId()
        ], Response::HTTP_CREATED, [], []);
    }


    /**
     * @Route("/conversations", name="getConversations", methods={"GET"})
     * @param Request $request
     */
    public function getConvs(Request $request): Response{
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        
        $users = $this->userRepository->findAll();
        $user = $this->getUser();
        $userName = $user->getUsername();
        $userEmail = $user->getEmail();
        return $this->render('home/messagerie.html.twig', [
            'controller_name' => 'ConversationController',
            'conversations' => $conversations,
            'users' => $users,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'messages' => null,
        ]);
    }

    /**
     * @Route("/conversations/messages/{id}", name="getMessages", methods={"GET"})
     * @param Request $request
     * @param Conversation $conversation
     * @return Response
     */
    public function getMessages(Request $request, Conversation $conversation, $id)
    {
        // can i view the conversation

        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->findMessageByConversationId($id);

        /**
         * @var $message Message
         */
        array_map(function ($message) {
            $message->setMine(
                $message->getUser()->getId() === $this->getUser()->getId()
                    ? true : false
            );
        }, $messages);

        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        $users = $this->userRepository->findAll();
        $user = $this->getUser();
        $userName = $user->getUsername();
        $userEmail = $user->getEmail();
        return $this->render('home/messagerie.html.twig', [
            'controller_name' => 'ConversationController',
            'conversations' => $conversations,
            'users' => $users,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'messages' => $messages,
        ]);
    }

    /**
     * @Route("/conversations/newMessage/", name="newMessage", methods={"POST"})
     * @param Request $request
     * @param Conversation $conversation
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function newMessage(Request $request, Conversation $conversation, SerializerInterface $serializer)
    {
        $user = $this->getUser();

        $recipient = $this->participantRepository->findParticipantByConverstionIdAndUserId(
            $conversation->getId(),
            $user->getId()
        );

        $content = $request->get('content', null);
        $message = new Message();
        $message->setContent($content);
        $message->setUser($user);

        $conversation->addMessage($message);
        $conversation->setLastMessage($message);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($message);
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
        $message->setMine(false);
        $messageSerialized = $serializer->serialize($message, 'json', [
            'attributes' => ['id', 'content', 'createdAt', 'mine', 'conversation' => ['id']]
        ]);
        $update = new Update(
            [
                sprintf("/conversations/%s", $conversation->getId()),
                sprintf("/conversations/%s", $recipient->getUser()->getUsername()),
            ],
            $messageSerialized,
            [
                sprintf("/%s", $recipient->getUser()->getUsername())
            ]
        );

        $this->publisher->__invoke($update);

        $message->setMine(true);
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        $users = $this->userRepository->findAll();
        $user = $this->getUser();
        $userName = $user->getUsername();
        $userEmail = $user->getEmail();
        $messages = $this->messageRepository->findMessageByConversationId($id);
        return $this->render('home/messagerie.html.twig', [
            'controller_name' => 'ConversationController',
            'conversations' => $conversations,
            'users' => $users,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'messages' => $messages,
        ]);
    }
    
}
