<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 03/12/2018
 * Time: 17:00
 */

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $articles = $repository->findBy([], ['publicationDate' =>'desc']);

        return $this->render(
            'admin/article/index.html.twig',
            [
                'articles' => $articles
            ]
        );
    }


    /**
     * @Route("/edition/{id}", defaults={"id" : null}, requirements={"id" : "\d+" })
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $orinalImage = null;

        if (is_null($id))
        {
            $article = new Article();
        }
        else
        {

            $article = $em->find(Article::class, $id);
            $orinalImage = $article->getImage();

            if (!is_null($orinalImage))
            {
                $article->setImage(
                    new File($this->getParameter('upload_dir').$orinalImage)
                );
            }

            if (is_null($article))
            {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                if (is_null($id))
                {
                    $article
                        ->setAuthor($this->getUser())
                        ->setPublicationDate(new \DateTime());
                    $this->addFlash('success', "L'article a bien été ajouté");
                }
                else
                {
                    $this->addFlash('success', "L'article a bien été modifié");
                }

                /**
                 * @var UploadedFile $image
                 */
                $image = $article->getImage();

                if (!is_null($image))
                {
                    // nom de l'image dans notre application
                    $filename = uniqid() . '.' . $image->guessExtension();

                    // équivalent de move_upload_file() en PHP tradi
                    $image->move(
                        // getParameter() fait partie de AbstractController
                        // getParameter() fait référence à parameters l.6 dans config/services.yaml
                        $this->getParameter('upload_dir'),
                        $filename
                    );

                    $article->setImage($filename);

                    if (!is_null($orinalImage))
                    {
                        unlink($this->getParameter('upload_dir').$orinalImage);
                    }
                }
                else    // Si on a pas reçu de nouvelle image en modification
                {
                    $article->setImage($orinalImage);
                }



                $em->persist($article);
                $em->flush();
                return $this->redirectToRoute('app_admin_article_index');
            }
            else
            {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }


        $comments = $article->getComments();

        return $this->render(
            'admin/article/edit.html.twig',
            [
                'form' => $form->createView(),
                'original_image' => $orinalImage,
                'comments'  => $comments
            ]
        );
    }



    /**
     * @Route("/suppression/{id}")
     */
    public function delete(Article $article)
    {
        $em=$this->getDoctrine()->getManager();

        // TODO: ajouter le remove sous condition de dépendance

        return $this->render(
            'admin/article/index.html.twig'
        );
    }


    /**
     * @Route("/suppression-commentaire/{id}")
     */
    public function commentDelete(Comment $comment)
    {
        if (!is_null($comment))
        {
            $em=$this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Le commentaire a bien été supprimer');
        }
        else
        {
            $this->addFlash('error', 'Ce commentaire n\'existe pas');
        }

        // TODO: ajouter le remove sous condition de dépendance

        return $this->redirectToRoute(
            'app_admin_article_edit',
            [
                'id' => $comment->getArticle()->getId()
            ]
        );
    }


    /**
     * @Route("/modalArticle/")
     */
    public function modalArticle(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find($request->request->get('articleId'));
        $tab=[];
        if ($request->isXmlHttpRequest())
        {
            $tab['content'] = $article->getContent();
        }
        else
        {
            echo "Ne reçois pas d'appel AJAX";
        }


        return new JsonResponse($tab);
    }
}