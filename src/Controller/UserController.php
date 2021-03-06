<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/** class UserController
 * @package App\Controller
 * @Route("/admin/user")
 */

class UserController extends Controller
{
    /**
     * @Route("/create", name="user_create")
     */
    public function create(Request $requete, UserPasswordEncoderInterface $passwordEncoder) {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $file = $form->get('file')->getData();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('avatar_directory'),
                $fileName
            );

            $user->setAvatar($fileName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'title' => 'Create user',
            'form' => $form->createView(),
            'button_label' => 'Create User',
        ]);
    }

    /**
     * @Route("/list", name="user_list")
     */
    public function list() {

        $userList = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/list.html.twig', [
            'userList' => $userList,
            'title' => 'List user',
        ]);
    }

    /**
     * @Route("/create/{id}", name="user_update")
     */
    public function update(Request $requete, $id, UserPasswordEncoderInterface $passwordEncoder)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $lastFileName = $user->getAvatar();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        $file = $form->get('file')->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('file')->getData() === null) {

                $user->setAvatar($lastFileName);
            }
            else {

                if (file_exists($this->getParameter('avatar_directory').'/'.$lastFileName)) {

                    unlink($this->getParameter('avatar_directory').'/'.$lastFileName);
                }

                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $user->setAvatar($fileName);

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );
            }
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'title' => 'Update user',
            'id' => $id,
            'form' => $form->createView(),
            'button_label' => 'Update User',
        ]);
    }

    /**
     * @Route("/delete/{id}", name="user_delete")
     */
    public function delete($id)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }
}