<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/** class FormationController
 * @package App\Controller
 * @Route("/admin/formation")
 */

class FormationController extends Controller
{
    /**
     * @Route("/create", name="formation_create")
     * @param Request $requete
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $requete) {

        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('formation_list');
        }

        return $this->render('formation/create.html.twig', [
            'title' => 'Create formation',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/list", name="formation_list")
     */
    public function list() {

        $formationList = $this
            ->getDoctrine()
            ->getRepository(Formation::class)
            ->findAll();

        return $this->render('formation/list.html.twig', [
            'formationList' => $formationList,
        ]);
    }
}
