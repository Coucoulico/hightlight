<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Doctrine\ORM\EntityManagerInterface;


class RegistrationController extends AbstractController
{
    private $passwordEncoder;
    private $authenticationUtils;
    private $existe_compte;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
       
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function index(Request $request,\Swift_Mailer $mailer)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the new users password
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            //check if there is no user compte associated to this Email
            $email=$user->getEmail();
            $search=$this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);

            //un compte est déja associé à l'adress fournie
            $existe = true;
            if(!$search)
            {   
                $existe = false;

                 // Set their role
                $user->setRoles(['ROLE_USER']);

                // On génère un token et on l'enregistre
                $user->setActivationToken(md5(uniqid()));

                // Save
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                //sending message to confirme the creation of the compte
                $message = (new \Swift_Message('Hello Email'))
                ->setFrom('hamza.baa1996@gamil.com')
                ->setTo($user->getEmail())
                ->setBody(
                $this->renderView(
                'emails/activation.html.twig', ['token' => $user->getActivationToken()]
                ),
                'text/html'
                );
                
                $mailer->send($message); 
                
            }
            return new Response(json_encode(['existe'=>$existe]));
            }            
            

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route("/activation/{token}", name="activation")
    */
    public function activation($token, UserRepository $users)
    {
        // On recherche si un utilisateur avec ce token existe dans la base de données
        $user = $users->findOneBy(['activation_token' => $token]);

        // Si aucun utilisateur n'est associé à ce token
        if(!$user){
            // On renvoie une erreur 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // On supprime le token
        $user->setActivationToken(null);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // On génère un message
        $this->addFlash('message', 'Utilisateur activé avec succès');

        // On retourne à l'accueil
        return $this->redirectToRoute('app_login');
    }


    






}