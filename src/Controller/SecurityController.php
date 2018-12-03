<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * Class SecurityController
 * @Route("/security")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                /**
                 * Encore le mot de passe, à partir de la config encoders de security.yaml
                 */
                $password = $userPasswordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                $em=$this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Votre compte a bien été créé');

                return $this->redirectToRoute('app_index_index');
            }
            else
            {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render('security/register.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @Route("/connexion")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        /**
         * traitement du formulaire par Security
         * Et remplis $_SESSION avec l'utilisateur si tout est bon
         */
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        dump($request->request->all());

        if (!empty($error))
        {
            $this->addFlash('error', 'Identifiant incorrect');
        }

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername
            ]
        );
    }
}
