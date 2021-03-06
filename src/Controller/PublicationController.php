<?php

namespace App\Controller;


use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicationController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(UserRepository $repo) :Response
    {
        $users = $repo->findBy(['isValidated' => 1]);
        $request = Request::createFromGlobals();

        return $this->render('publication/index.html.twig', [
            'controller_name' => 'PublicationController',
            'request' => $request,
            'users' => $users
        ]);
    }

    /**
     * @Route("/publish", name="publication_publish")
     */
    public function publish()
    {
        return $this->render('publication/publish.html.twig', [
            'controller_name' => 'PublicationController',
        ]);
    }
}
