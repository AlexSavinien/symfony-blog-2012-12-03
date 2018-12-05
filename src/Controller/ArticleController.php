<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @param Article $article
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/{id}")
     */
    public function index(Article $article, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * afficher toutes les informations de l'article avec l'image s'il y en a une
         *
         * Sous l'article, si l'utilisateur n'est pas connecté, l'inviter à le faire pour pouvoir écrire un commentaire.
         * Sinon, lui afficher un forumaire avec un textarea pour pouvoir écrire un commentaire
         *
         * Nécessite une entité Comment :
         * - content (text)
         * - publicationDate(dateTime)
         * - user(manyToOne)
         * - article(manyToOne)
         *
         * FormType pour les commentaires avec le textarea (not blank)
         *
         * En dessous, afficher les commentaires par date décroissante avec :
         * - nom utilisateur,
         * - date publication
         * - contenu du message
         */


        // =============================== FORMULAIRE ======================================
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            if ($form->isValid())
            {
                $comment
                    ->setPublicationDate(new \DateTime())
                    ->setUser($this->getUser())
                    ->setArticle($article)
                ;

                $em->persist($comment);
                $em->flush();
                $this->addFlash('success', 'Votre commentaire a bien été enregistré');
            }
            else
            {
                $this->addFlash('error', 'Le commentaire contient des erreurs');
            }
        }


        // =========================== LISTE COMMENTAIRES ==================================
        $comments = $article->getComments();



        return $this->render('article/index.html.twig', [
            'article'   => $article,
            'form'      => $form->createView(),
            'comment'   => $comment,
            'comments'  => $comments
        ]);
    }
}
