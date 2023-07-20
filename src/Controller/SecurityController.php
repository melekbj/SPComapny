<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;


class SecurityController extends AbstractController
{

    #[Route('/register', name: 'app_registration')]
    public function registration(Request $request, PersistenceManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHashed): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHashed->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            if ($user->getRoles() == 'ROLE_SUPER_USER') {
                $user->setEtat('pending');
            }
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $request->getSession()->getFlashBag()->add('success', 'Account created successfully.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/register.html.twig', [
            'registerForm' => $form->createView(),
        ]);
    }


    
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
    
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $translatedError = null;
    
        // Custom error message for "pending" accounts
        if ($error instanceof CustomUserMessageAuthenticationException) {
            $translatedError = $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security');
        }
    
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
    
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'translated_error' => $translatedError, // Pass the translated error to the template
        ]);
    }
    

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/reset-password', name:'forgotten_password')]
    public function forgottenPassword(Request $request,UserRepository $usersRepository,TokenGeneratorInterface $tokenGenerator,EntityManagerInterface $entityManager,SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //On va chercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if($user){
                // On génère un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // On crée les données du mail
                $context = compact('url', 'user');
                
                // Envoi du mail
                $mail->send(
                    'melekbejaoui29@gmail.com', 'Secure print',
                    $user->getEmail(),
                    'Réinitialisation de mot de passe',
                    'password_reset',
                    $context    
                );

                $request->getSession()->getFlashBag()->add('success', 'Email envoyé avec succès. Veuillez vérifier votre boîte de réception.');
                return $this->redirectToRoute('app_login');

            }
            // $user est null
            $request->getSession()->getFlashBag()->add('error', 'We could not find a record of this email in our system. Please check that you have entered the correct email address and try again.');
            return $this->redirectToRoute('forgotten_password');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route('/reset-password/{token}', name:'reset_pass')]
    public function resetPass(string $token,Request $request,UserRepository $usersRepository,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): Response
    {
        // On vérifie si on a ce token dans la base
        $user = $usersRepository->findOneByResetToken($token);
        
        if($user){
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                // On efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                // $this->addFlash('success', 'Mot de passe changé avec succès');
                // return $this->redirectToRoute('app_login');
                $request->getSession()->getFlashBag()->add('success', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }
        // $this->addFlash('danger', 'Jeton invalide');
        // return $this->redirectToRoute('app_login');
        $request->getSession()->getFlashBag()->add('error', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }

















}
