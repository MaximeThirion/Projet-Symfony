<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
     * @param Request $requete
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $requete) {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

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
}