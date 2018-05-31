<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/** class FormationController
 * @package App\Controller
 * @Route("/admin/course")
 */

class CourseController extends Controller
{
    /**
     * @Route("/create", name="course_create")
     */
    public function create(Request $requete) {

        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $course = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('course_list');
        }

        return $this->render('course/create.html.twig', [
            'title' => 'Create course',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/list", name="course_list")
     */
    public function list() {

        $courseList = $this
            ->getDoctrine()
            ->getRepository(Course::class)
            ->findAll();

        return $this->render('course/list.html.twig', [
            'courseList' => $courseList,
            'title' => 'List course',
        ]);
    }
}
