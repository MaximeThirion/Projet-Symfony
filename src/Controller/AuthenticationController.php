<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/auth")
 */
class AuthenticationController extends Controller
{
    /**
     * @Route("/registration/activation/{code}/{id}", name="authentication_registration_activation")
     */
    public function activation($code, $id)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $message = 'Account activated';

        if ($code === $this->createCode($user)) {

            $user->setActive(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            $message = 'Wrong activation code';
        }

        return $this->render('authentication/activation.html.twig', [
            'message' => $message,
        ]);
    }

    /**
     * @Route("/registration/success", name="authentication_registration_success")
     */
    public function registrationSuccess()
    {

        return $this->render('authentication/registration_success.html.twig', [
            'title' => 'Success',
        ]);
    }

    /**
     * @Route("/registration", name="authentication_registration")
     */
    public function registration(Request $requete, \Swift_Mailer $mailer, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($requete);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $file = $form->get('file')->getData();
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('avatar_directory'),
                $fileName
            );

            $user->setAvatar($fileName);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $code = $this->createCode($user);

            $activation_link = $this->generateUrl(
                'authentication_registration_activation',
                ['code' => $code, 'id' => $user->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $message = (new \Swift_Message('Test email'))
                ->setFrom('activation@exemple.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/registration.html.twig',
                        ['activation_link' => $activation_link]
                    ),
                    'text/html'
                );
            $mailer->send($message);

            return $this->redirectToRoute('authentication_registration_success');
        }

        return $this->render('authentication/registration.html.twig', [
            'title' => 'Register',
            'form' => $form->createView(),
            'button_label' => 'Register',
        ]);
    }

    private function createCode(User $user)
    {
        return sha1('fs5g51sgsv5svss2vb2tn2y1t2ng3f6n' . $user->getId() . $user->getEmail());
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('authentication/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }
}
