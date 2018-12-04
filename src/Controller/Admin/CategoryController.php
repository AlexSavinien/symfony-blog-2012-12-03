<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 30/11/2018
 * Time: 14:54
 */

namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 * @Route("/categorie")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Category::class);


        // findBy([], 'colonne'=>'asc') revient à faire un findAll
        // ou l'on peut trier le résultat sur la colonne de notre choix
        $categories = $repository->findBy([], ['name' =>'asc']);


        return $this->render(
            'admin/category/index.html.twig',
            [
                'categories' => $categories
            ]
        );
    }


    /**
     * {id} est optionne et doit être un nombre
     * @Route("/edition/{id}", defaults={"id" : null}, requirements={"id" : "\d+"})
     * @param Request $request
     * @return Response | Request
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();


        // Si pas d'id, on créé une nouvelle category qui prendra le post du formulaire en valeur
        if(is_null($id))
        {
            $category = new Category();
        }
        // Si id non null, alors on cherche la catégorie existante avec l'id correspondant
        else
        {

            $category = $em->find(Category::class, $id);
            if(is_null($category))
            {
                throw new NotFoundHttpException();
            }
        }


        // Création du formulaire lié à la catégorie
        $form = $this->createForm(CategoryType::class, $category);

        // La formulaire analyse la requete HTTP et traite le formulaire s'il a été soumis
        $form->handleRequest($request);

        // Si le formulaire a été envoyé
        if ($form->isSubmitted())
        {
            dump($category);

            // Si les validations à partir des annotations dans l'entité Category sont ok
            if ($form->isValid())
            {
                // Enregistrement de notre nouvelle categorie en BDD
                $em->persist($category);
                $em->flush();

                // Message de confirmation
                $this->addFlash('success', 'La catégorie a été enregistrée.');
                // Redirection vers la liste
                return $this->redirectToRoute('app_admin_category_index');
            }
            else
            {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }


        return $this->render(
            'admin/category/edit.html.twig',
            [
                // passage du formulaire au template
                'form'=>$form->createView()
            ]
        );
    }

    /**
     * @Route("/suppression/{id}")
     * @param Category $category
     * @return Response
     */
    public function delete(Category $category)
    {
        $em = $this->getDoctrine()->getManager();

        if ($category->getArticle()->count() == 0)
        {
            $em->remove($category);
            $em->flush();
            $this->addFlash(
                'success',
                'La catégorie a bien été supprimée'
            );
        }
        else
        {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer une catégorie utilisé par un article');
        }

        return $this->redirectToRoute('app_admin_category_index');
    }
}