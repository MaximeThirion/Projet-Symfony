<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Form\ExerciceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/** class ExerciceController
 * @package App\Controller
 * @Route("/admin/exercice")
 */
class ExerciceController extends Controller
{
    /**
     * @Route("/create", name="exercice_create")
     */
    public function create(Request $requete)
    {

        $exercice = new Exercice();
        $form = $this->createForm(ExerciceType::class, $exercice);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $exercice = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exercice);
            $entityManager->flush();

            return $this->redirectToRoute('exercice_list');
        }

        return $this->render('exercice/create.html.twig', [
            'title' => 'Create exercice',
            'form' => $form->createView(),
            'button_label' => 'Create Exercise'
        ]);
    }

    /**
     * @Route("/list", name="exercice_list")
     */
    public function list()
    {

        $exerciceList = $this
            ->getDoctrine()
            ->getRepository(Exercice::class)
            ->findAll();

        return $this->render('exercice/list.html.twig', [
            'exerciceList' => $exerciceList,
            'title' => 'List exercice',
        ]);
    }

    /**
     * @Route("/create/{id}", name="exercice_update")
     */
    public function update(Request $requete, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $exercice = $entityManager->getRepository(Exercice::class)->find($id);

        $form = $this->createForm(ExerciceType::class, $exercice);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('exercice_list');
        }

        return $this->render('exercice/create.html.twig', [
            'title' => 'Update exercice',
            'id' => $id,
            'form' => $form->createView(),
            'button_label' => 'Update Exercise'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="exercice_delete")
     */
    public function delete($id)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $exercice = $entityManager->getRepository(Exercice::class)->find($id);

        $entityManager->remove($exercice);
        $entityManager->flush();

        return $this->redirectToRoute('exercice_list');
    }
}
