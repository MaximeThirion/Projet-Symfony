<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * @Route("/search", name="search")
     */
    public function search()
    {
        if (isset($_POST['rechercher'])) {

            $entityManager = $this->getDoctrine()->getManager();

            $requete = "SELECT 'User' type, concat(firstname,' ', lastname) as label FROM `user` WHERE concat(firstname, lastname) LIKE :recherche
                        UNION
                        SELECT 'Exercice' type, label FROM `exercice` WHERE label LIKE :recherche
                        UNION
                        SELECT 'Course' type, label FROM `course` WHERE label LIKE :recherche
                        UNION
                        SELECT 'Formation' type, label FROM `formation` WHERE label LIKE :recherche";
            $statement = $entityManager->getConnection()->prepare($requete);
            $statement->bindValue('recherche', '%'.$_POST['input_rechercher'].'%');
            $statement->execute();

            $donnees = $statement->fetchAll();
        }

        return $this->render('search/search.html.twig', [
            'title' => 'Search',
            'donnees' => $donnees,
        ]);
    }
}