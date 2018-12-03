<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 03/12/2018
 * Time: 17:00
 */

namespace App\Controller\Admin;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Admin
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Article::class);


        // findBy([], 'colonne'=>'asc') revient à faire un findAll
        // ou l'on peut trier le résultat sur la colonne de notre choix
        $articles = $repository->findBy([], ['name' =>'asc']);

        return $this->render(
            'admin/article/index.html.twig',
            [
                'articles' => $articles
            ]
        );
    }
}