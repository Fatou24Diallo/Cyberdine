<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/user", name="useracccount")
     */
    public function userPage(Request $request): Response
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userName = $user->getUsername();
        $userEmail = $user->getEmail();

        return $this->render('home/userpage.html.twig', [
            'controller_name' => 'HomeController',
            'user_name' => $userName,
            'user_email' => $userEmail,
        ]);
    }

    /**
     * @Route("/messagerie", name="messagerie")
     */
    public function messagerie(Request $request): Response
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userName = $user->getUsername();
        $userEmail = $user->getEmail();

        return $this->render('home/messagerie.html.twig', [
            'controller_name' => 'HomeController',
            'user_name' => $userName,
            'user_email' => $userEmail,
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
