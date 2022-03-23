<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    # Accueil
    #[Route(path: '/', name: 'home')]
    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig');
    }
}