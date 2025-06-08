<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Entity\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        if ($this->getUser() === null) {
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'You do not have permission to access this page.');
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $pwh): Response
    {
        $user = new User(); 
        // to nable save modification of the user without password
        $form = $this->createForm(UserForm::class, $user, ['include_password' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $count = $entityManager -> getRepository(User::class)->count([]);
            $pass= $pwh->hashPassword($user, $user->getPassword());
            $user->setPassword($pass);
            // Example: Find the CUSTOMER role using Doctrine
            $customerRole = $entityManager->getRepository(Role::class)->findOneBy(['code' => 'CUSTOMER']);
            if ($customerRole === null) {
                $this->addFlash('error', 'No role found');
                return $this->redirectToRoute('app_user_new');
            }

            $user->setRole($customerRole);
            $user->setCode('USER' .$count + 1);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success','Your account has been created successfully');

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        if ($this->getUser() === null || !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null || !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === null || !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
