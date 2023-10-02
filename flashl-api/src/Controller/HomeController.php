<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function getHome(Request $request): Response
    {

        if (!$this->getUser()) {
            $this->addFlash('danger', 'You need to be logged in to access this page');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        $hash = $request->query->get('hash');
        $expires = $request->query->get('expires');

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $user,
            'hash' => $hash,
            'expires' => $expires,
        ]);
    }
}
