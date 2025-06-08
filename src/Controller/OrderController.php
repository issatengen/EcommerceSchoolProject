<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderForm;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route(name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        if ($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        $order = new Order();
        $form = $this->createForm(OrderForm::class, $order);
        $form->handleRequest($request);
        $count = $entityManager->getRepository(Order:: class)->count([]);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser() !== null) {
                $order->setCode('CMD' . $count);
                // $order->setDate(new \DateTime());
                $order->setUser($this->getUser());
            }
            $entityManager->persist($order);
            $entityManager->flush();
            $this->addFlash('success', 'Order created successfully you can now add products and purchase');

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        if ($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(OrderForm::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null ) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

}
