<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

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
    public function index(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the new users password
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            //check if there is no user compte associated to this Email
            $email=$user->getEmail();
            $search=$this->getDoctrine()->getRepository(User::class);(['email' => $email]);

            //un compte est déja associé à l'adress fournie
            if($search)
            {  
                return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
                
            }

            else{
                 // Set their role
            $user->setRoles(['ROLE_USER']);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            
            return $this->redirectToRoute('app_login');
            }
           
            
            
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}