<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Item;
use App\Repository\ItemRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ItemRepository $repo): Response
    {
        // Example of using EntityManagerInterface to fetch items
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'items' => $repo->findALL(),
        ]);
    }
}
