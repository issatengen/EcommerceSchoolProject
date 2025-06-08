<?php

namespace App\Controller;

use App\Entity\OrderLine;
use App\Entity\Item;
use App\Entity\Order;
use App\Entity\User;
use App\Form\OrderLineForm;
use App\Repository\OrderLineRepository;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order/line')]
final class OrderLineController extends AbstractController
{
    #[Route(name: 'app_order_line_index', methods: ['GET'])]
    public function index(OrderLineRepository $orderLineRepository): Response
    {
        return $this->render('order_line/index.html.twig', [
            'order_lines' => $orderLineRepository->findAll(),
        ]);
    }

    #[Route('/new{id}', name: 'app_order_line_new', methods: ['GET', 'POST'])]
    public function new(Request $request, $id, EntityManagerInterface $entityManager): Response
    {

        $desiredTimezone = new \DateTimeZone('Africa/Douala');

        $orderLine = new OrderLine();
        $form = $this->createForm(OrderLineForm::class, $orderLine);
        $form->handleRequest($request);

        $item=$entityManager->getRepository(Item::class)->find($id);
        // $lastcmd=$entityManager->getRepository(Order::class)->find();

        $price=$entityManager->createQueryBuilder()
            ->select('i.price')
            ->from('App\Entity\Item', 'i')
            ->where('i.id=:id')
            ->setParameter('id', $item)
            ->getQuery()
            ->getSingleScalarResult();

        $lastOrderid = $entityManager->createQueryBuilder()
            ->select('MAX(o.id)')
            ->from('App\Entity\Order', 'o')
            ->where('o.user = :userId')
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()
            ->getSingleScalarResult();

        // Get the user's most recent order
        $lastOrder = $entityManager->getRepository(Order::class)->findOneBy(
            ['user' => $this->getUser()],  // Fixed: getUser() should be $this->getUser()
            ['date' => 'DESC']
        );

        if (!$lastOrder) {
            $this->addFlash('error', 'Please create an order first');
            return $this->redirectToRoute('app_order_new');  // Fixed: redirectToRoute (correct casing)
        }

        // Compare current hour with order's hour
        $currentDateTime = new \DateTime('now', $desiredTimezone);
        $currentHour = $currentDateTime->format('Y-m-d H'); // No 'e' needed for comparison

        $lastOrderHour = $lastOrder->getDate()->format('Y-m-d H');

        if ($currentHour != $lastOrderHour) {
            $this->addFlash('error', 'Please create a new order for the current hour'. $currentHour.' '. $lastOrderHour);
            return $this->redirectToRoute('app_order_new');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $quantity=$form->get('quantity')->getData();

            $orderLine->setAmount($quantity * $price);
            $orderLine->setItem($item);
            $orderLine->setOrders($lastOrder);
            
            $entityManager->persist($orderLine);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order_line/new.html.twig', [
            'order_line' => $orderLine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_line_show', methods: ['GET'])]
    public function show(OrderLine $orderLine): Response
    {
        return $this->render('order_line/show.html.twig', [
            'order_line' => $orderLine,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_line_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OrderLine $orderLine, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderLineForm::class, $orderLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_order_line_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order_line/edit.html.twig', [
            'order_line' => $orderLine,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_line_delete', methods: ['POST'])]
    public function delete(Request $request, OrderLine $orderLine, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$orderLine->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($orderLine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_line_index', [], Response::HTTP_SEE_OTHER);
    }
}
