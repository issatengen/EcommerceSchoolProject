<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Customer;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        if($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        $userCount = $em->getRepository('App\Entity\User')->count([]);
        $orderCount = $em->getRepository('App\Entity\Order')->count([]);
        $productCount = $em->getRepository('App\Entity\Item')->count([]);

        // Assuming customers are users with a specific role, e.g., ROLE_CUSTOMER
        $customerCount = $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('App\Entity\User', 'u')
            ->leftJoin('u.role', 'r')
            ->where('r.label = :role')
            ->setParameter('role', 'ROLE_CUSTOMER')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin_dashboard/index.html.twig', [
            'userCount' => $userCount,
            'orderCount' => $orderCount,
            'customerCount' => $customerCount,
            'productCount' => $productCount,
        ]);
    }
}
