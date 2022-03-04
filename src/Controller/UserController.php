<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * @Route("/user/profile", name="user_show_profile")
     */
    public function showProfileIndex(Request $request): Response
    {
        return $this->render('user/show.html.twig', [
        ]);
    }

    /**
     * @Route("/user/register", name="user_register")
     */
    public function createAccount(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) echo "YES";
        if ($form->isSubmitted() && $form->isValid()) {
            $user           = $form->getData();
            $em             = $this->doctrine->getManager();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_show_profile', ['register' => 'success']);
        }
        return $this->renderForm('user/register.html.twig', [
            'form'      => $form,
        ]);
    }
}