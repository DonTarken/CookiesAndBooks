<?php

namespace App\Controller;

// use App\Security\LoginFormAuthenticator;
use App\Entity\User;
use App\Form\RegistrationType;

use App\Form\EditionProfileType;
use App\Repository\UserRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/member/registration", name="user_registration")
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, Filesystem $filesystem)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $dir = 'uploads';
            $filename = 'avatar'.'.jpg';
                try {
                $filesystem->mkdir($dir.'/'.$user->getUsername().'');
                $filesystem->mkdir($dir.'/'.$user->getUsername().'/avatar');
                $filesystem->mkdir($dir.'/'.$user->getUsername().'/banner');
                $filesystem->copy('assets/img/avatar/avatar.jpg', $dir.'/'.$user->getUsername().'/avatar/'.$filename);
                } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at ".$exception->getPath();
                }

            $user->setAvatar(''.$filename.'');

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $token = md5(uniqid(mt_rand()));

            $user->setPassword($hash);
            $user->setCreatedAt(new \DateTime());
            $user->setApiToken($token);
            $user->setIsValidated(0);

            $manager->persist($user);
            $manager->flush();

            $user_token = $user->getApiToken();

            $url = "http://127.0.0.1:8000/member/confirm/". $user_token ."";

            $message = (new \Swift_Message('Bienvenue sur Cookies And Books !'))
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
     * @Route("/member/confirm/{token}", name="confirm_account")
     */
    public function confirmAccount($token, ObjectManager $manager, UserRepository $repo) 
    {

        $user = $repo->findOneBy([
                         'apiToken' => $token
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
            'error' => $error
        ]);
    }

    /**
     * @Route("/member/logout", name="user_logout")
     */
    public function logout() {}

    /**
     * @Route("/user/{username}", name="user_profile")
     */
    public function profile(User $user) {

        $is_user = false;

        if($this->getUser() && $user->getUsername() === $this->getUser()->getUsername()){
            $is_user = true;
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'is_user' => $is_user,
            'test' => $this->getUser()
        ]);
    }

    /**
     * @Route("/user/{username}/edit", name="user_edit")
     */
    public function edit_profile(User $user, ObjectManager $manager, UserPasswordEncoderInterface $encoder, Request $request, Filesystem $filesystem) {

        if($user->getUsername() === $this->getUser()->getUsername()) {
            $dir = 'uploads';
            if(!$filesystem->exists($dir.'/'.$user->getUsername().'')){
                try {
                $filesystem->mkdir($dir.'/'.$user->getUsername().'');
                } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at ".$exception->getPath();
                }
            }

            $form = $this->createForm(EditionProfileType::class, $user);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                if($user->getNewPassword() !== NULL){
                    $hash = $encoder->encodePassword($user, $user->getNewPassword());
                    $user->setPassword($hash);
                }
                if($user->getAvatar() !== NULL) {

                    try {
                        $filesystem->mkdir($dir.'/'.$user->getUsername().'/avatar');
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                    $file_avatar_profile = $form->get('avatar')->getData();
                    $filename_avatar_profile = 'avatar.jpg';
                    $file_avatar_profile->move($dir.'/'.$user->getUsername().'/avatar', $filename_avatar_profile);
                    $user->setAvatar(''.$filename_avatar_profile.'');
                } else {
                    $user->setAvatar('avatar.jpg');
                }
                if($user->getBanner() !== NULL) {
                    try {
                        $filesystem->mkdir($dir.'/'.$user->getUsername().'/banner');
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                    $file_banner_profile = $form->get('banner')->getData();
                    $filename_banner_profile = 'banner'.'.jpg';
                    $file_banner_profile->move($dir.'/'.$user->getUsername().'/banner', $filename_banner_profile);
                    $user->setBanner(''.$filename_banner_profile.'');
                } else {
                    $user->setBanner('banner.jpg');
                }
                $manager->persist($user);
                $manager->flush();
            }

            return $this->render('user/edit.html.twig', [
                'formEdit' => $form->createView(),
                'user' => $user
            ]);
        }
        else {
            return $this->redirectToRoute('home', [], 301);
        }
    }
}
