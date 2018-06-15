<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/** class UserController
 * @package App\Controller
 * @Route("/admin/user")
 */

class UserController extends Controller
{
    /**
     * @Route("/create", name="user_create")
     */
    public function create(Request $requete) {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $file = $form->get('avatar')->getData();
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
    public function update(Request $requete, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $user->setAvatar(
            new File($this->getParameter('avatar_directory').'/'.$user->getAvatar())
        );

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('avatar')->getData();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('avatar_directory'),
                $fileName
            );

            $user->setAvatar($fileName);

            $entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'title' => 'Update user',
            'id' => $id,
            'form' => $form->createView()
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