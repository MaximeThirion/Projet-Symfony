<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index()
    {

        return $this->render('profile/profile.html.twig', [
            'title' => 'Profile',
        ]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function edit(Request $requete, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $lastFileName = $user->getAvatar();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {

                $user->setAvatar($lastFileName);
            } else {

                if (file_exists($this->getParameter('avatar_directory') . '/' . $lastFileName)) {

                    unlink($this->getParameter('avatar_directory') . '/' . $lastFileName);
                }

                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $user->setAvatar($fileName);

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );
            }
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/edit.html.twig', [
            'title' => 'Edit',
            'form' => $form->createView(),
            'button_label' => 'Update',
        ]);
    }
}