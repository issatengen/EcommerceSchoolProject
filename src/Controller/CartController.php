<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Item;
use App\Entity\OrderLine;
use App\Form\OrderForm;
use App\Repository\OrderRepository;
use App\Repository\OrderLineRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\User;
use App\Form\OrderLineForm;
use App\Repository\UserRepository;


final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_order_singleC', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        $orders = $entityManager->getRepository(Order::class)->findBy(
            ['user' => $this->getUser()],
            ['id' => 'DESC']
        );

        return $this->render('cart/index.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/cart/show/{id}', name: 'app_show_order', methods: ['GET'])]
    public function show($id, EntityManagerInterface $entityManager): Response
    {

        $orderId=$entityManager->getRepository(Order::class)->find($id);
        
        $orderLines=$entityManager->getRepository(OrderLine::class)->findBy(['orders' => $orderId ]);

        $count=$entityManager->createQueryBuilder()
            ->select('SUM(l.quantity)')
            ->from(OrderLine::class, 'l')
            ->where('l.orders = :orderId')
            ->setParameter('orderId', $orderId->getId())
            ->getQuery()
            ->getSingleScalarResult();

        $totalAmount=$entityManager->createQueryBuilder()
            ->select('SUM(l.amount)')
            ->from(OrderLine::class, 'l')
            ->where('l.orders = :orderId')
            ->setParameter('orderId', $orderId->getId())
            ->getQuery()
            ->getSingleScalarResult();


        return $this->render('cart/show.html.twig', [
            'orderLines' => $orderLines,
            'total' => $count,
            'totalAmount' => $totalAmount,
        ]);
    }

    #[Route('remove/{id}', name: 'app_order_item_delete', methods: ['POST'])]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(OrderLine::class)->find($id);
        $entityManager->remove($item);
        $entityManager->flush();
        $this->addFlash('success', 'Item deleted successfully');

        return $this->redirectToRoute('app_order_singleC', [], Response::HTTP_SEE_OTHER);
    }
    
}
