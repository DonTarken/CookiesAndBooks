<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/member/registration", name="user_registration")
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $token = md5(uniqid(mt_rand()));

            $user->setPassword($hash);
            $user->setCreatedAt(new \DateTime());
            $user->setApiToken($token);
            $user->setIsValidated(0);

            $manager->persist($user);
            $manager->flush();

            $user_token = $user->getApiToken();
            $user_username = $user->getUsername();

            $url = "http://127.0.0.1:8000/member/confirm/". $user_token ."/". $user_username ."";

            $message = (new \Swift_Message('Hello Email'))
                    ->setFrom('send@example.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'emails/registration.html.twig',
                            ['url' => $url]
                        ),
                        'text/html'
                    );
            
            $mailer->send($message);

            return $this->redirectToRoute('user_login');
        }


        return $this->render('user/registration.html.twig', [
            'formRegistration' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/login", name="user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/member/confirm/{token}/{username}", name="confirm_account")
     */
    public function confirmAccount($token, $username, ObjectManager $manager) 
    {
        
        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->findOneBy([
                         'apiToken' => $token,
                         'username' => $username
                     ]);

        $error = 0;

        if(!$user){
            $error = 1;
        }else{
            if($user->getIsValidated()){
                $error = 2;
            }else{
                $user->setIsValidated(1);
                $manager->persist($user);
                $manager->flush();
            }
        }

        return $this->render('emails/confirmAccount.html.twig', [
            'user' => $username,
            'error' => $error
        ]);
    }

    /**
     * @Route("/member/logout", name="user_logout")
     */
    public function logout() {}
}
