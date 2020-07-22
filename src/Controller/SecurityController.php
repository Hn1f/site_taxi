<?php

namespace App\Controller;

use DateTimeZone;
use App\Entity\User;
use App\Entity\Trajet;
use App\Form\LoginType;
use App\Entity\Commentaire;
use App\Entity\Verification;
use SMS\SMSPartnerAPI; 
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{   
    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer){
        $user= new User;
        $verification= new Verification;
        $form = $this->createForm(RegistrationType::class, $user);
        $timezone = new DateTimeZone('Europe/Paris');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $entityManager->persist($user);
            $entityManager->flush();
            $id=$user->getId();
            $verification->setUserId($id);
            $token=( rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
            $verification->setToken($token);
            $verification->setCreatedAt(new \DateTime( 'now', $timezone));
            $entityManager->persist($verification);
            $entityManager->flush();
            $email=$user->getEmail();
            $prenom=$user->getFirstname();
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('verification@taxischolet.fr')
                ->setTo($email)
                ->setBody(
                $this->renderView(
                    'email/email.html.twig',
                    ['prenom' => $prenom,
                    'token'=>$token,
                    ]
                ),
                'text/html'
             );
        $mailer->send($message);
        return $this->render('security/email.html.twig'); 

    }
        return $this->render('security/inscription.html.twig', [
        'formInscription' => $form->createView(),
        ]);
}

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils,  SessionInterface $session,  EntityManagerInterface $entityManager): Response
    { 
        $user=$this->getUser();
        $form = $this->createForm(LoginType::class);
        $trajet = $session->get('trajet');
        if(isset($trajet)){
            $depart = $trajet->getDepart();
            $arrive =$trajet->getArrive(); 
            $client = new CurlHttpClient();
            $response = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$depart.'&destinations='.$arrive.'&key=AIzaSyDlAsntkQzHrbKeT0GQkaLSSdWIjIqQeqk'); 
       
        $contents = $response->getContent();
        $contents=json_decode($contents);
        $heure= ($trajet->getHour()); 
        $heure=$heure->format('H');
        dump($trajet);
        dump($heure);
        if(($heure>=19) || ($heure<7)){
            $prix=((($contents->rows[0]->elements[0]->distance->value/1000)*2.76)+2.80);
        } 
        else{
            $prix=((($contents->rows[0]->elements[0]->distance->value/1000)*1.84)+2.80);
        }
        $prix=((($contents->rows[0]->elements[0]->distance->value/1000)*1.84)+2.80);
        $prixmin= $prix-($prix*20)/100;
        $prixmax= $prix+($prix*20)/100;
        $prixmin = round($prixmin,2);
        $prixmax = round($prixmax,2);
        dump($prixmin); 
        dump($prixmax); 
        $prix = round($prix,2);
        $prixresa= ($prix*20)/100;   
        $prixresa = round($prixresa, 2);
        $trajet->setPrix($prix);
        }  
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        if(isset($user) && isset($trajet)){
            $depart1= $trajet->getAdresse1()." ". $trajet->getCp1(); 
            $arrive1= $trajet->getAdresse2()." ". $trajet->getCp2(); 
            $id=$user->getId(); 
            $prenom=$user->getFirstname();
            $nom=$user->getLastName();  
            $trajet->setUserId($id);
            $entityManager->persist($trajet);
            $entityManager->flush();
            $smspartner = new SMSPartnerAPI (false);
            //check credits
            $result = $smspartner->checkCredits('?apiKey=6c30a24b177c04b53e4160a0ddde0ce091f9d66a');
            //send SMS
            $fields = array(
                "apiKey"=>"6c30a24b177c04b53e4160a0ddde0ce091f9d66a",
                "phoneNumbers"=>"0769849455",
                "message"=>"Un trajet de ".$depart1." à ".$arrive1." vien d'être réservé pour un total de ".$prix."€ par monsieur ".$nom." ".$prenom.".",
                "sender" => "AMTaxi",
            );
            // $result = $smspartner->sendSms($fields);
            //get delivery
            // $result = $smspartner->checkStatusByNumber('?apiKey=6c30a24b177c04b53e4160a0ddde0ce091f9d66a&messageId=666&phoneNumber=0769849455');
            return $this->redirectToRoute('paiement');
        }

        if(isset($trajet)){
        return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
         'error' => $error,
         'prixmin' => $prixmin,
         'prixmax' => $prixmax,
         'prixresa'=>$prixresa,   
         'form' => $form->createView(),
         ]);
        }
        else{
            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                 'error' => $error,
                 'form' => $form->createView(),
         ]);

        }
    }

    /**
     * @Route("/mon-compte", name="mon_compte")
     */
    public function AccountEditiion(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('security/moncompte.html.twig', [
            'form'=> $form->createView(),
        ]);
    }

    /**
     * @Route("/confirmation/{token}", name="confirmation")
     */

     public function confirmation($token, EntityManagerInterface $entityManager){
        $repo = $this->getDoctrine()->getRepository(Verification::class);

        $validation= $repo->findOneBy(['token'=>$token]);
        if($validation != NULL){
            $id=$validation->getUserId();

            $repo = $this->getDoctrine()->getRepository(User::class);
            $user=$repo->find($id);
            $user->setRoles(['ROLES_USER']);
            $prenom = $user->getFirstName();
            $entityManager->persist($user);
            $entityManager->flush();
            $entityManager->remove($validation);
            $entityManager->flush();
            


            return $this->render('security/validation.html.twig', [
            'prenom' =>$prenom,
            ]);
        }

        else{
            return $this->render('security/error.html.twig');
        }
        
    }

    /**
     * @Route("/mes-trajets", name="mesTrajets")
     */
    public function trajet()
    {
        $user = $this->getUser(); 
        $id = $user->getId();
        $repo = $this->getDoctrine()->getRepository(Trajet::class);
        $trajets = $repo->findBy(['user_id'=>$id]); 
        dump($trajets); 
        return $this-> render('security/trajet.html.twig', [
            'trajets' => $trajets
        ]); 
    }

     /**
     * @Route("/suppression", name="supprimerUser")
     */
    public function delete()
    {
        $user = $this->getUser();
        $id = $user->getId();
        $repo = $this->getDoctrine()->getRepository(Trajet::class);
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();  
        return $this->render('security/supprimer.html.twig'); 
    }

    /**
     * @Route("/mot-de-passe", name="mdp")
     */
    public function reinitialisation(Request $request, \Swift_Mailer $mailer, EntityManagerInterface $entityManager)
    {

      $form = $this->createFormBuilder()
        ->add('email', TextType::class)
        ->add('reinitialiser', SubmitType::class)
        ->getForm();
        $form->handleRequest($request); 
        
        if($form->isSubmitted() && $form->isValid()){
            $email= $form->getViewData($form); 
            $email=$email['email'];
            $repo = $this->getDoctrine()->getRepository(User::class);

            $user= $repo->findOneBy(['email'=>$email]);

            if($user!==NULL){
                $token=( rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
                $verification= new Verification;
                $timezone = new DateTimeZone('Europe/Paris');
                $id=$user->getId();
                $verification->setUserId($id);
                $verification->setToken($token); 
                $verification->setCreatedAt(new \DateTime( 'now', $timezone));
                $prenom=$user->getFirstName(); 
                $entityManager->persist($verification);
                $entityManager->flush();
                $message = (new \Swift_Message('Hello Email'))
                ->setFrom('motdepasse@taxischolet.fr')
                ->setTo($email)
                ->setBody(
                $this->renderView(
                    'email/mdpperdu.html.twig',
                    ['prenom' => $prenom,
                    'token'=>$token,
                    ]
                ),
                'text/html'
             );
        $mailer->send($message);

        return $this->render('security/mdplien.html.twig'); 
            }
        }
        
        return $this->render('security/mdp.html.twig', [
            'form'=> $form->createView(), 
        ]); 
    
    }

    /**
     * @Route("/mot-de-passe/{token}", name="mdpchange")
     */

    public function changeMdp($token, EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $encoder){

        $form = $this->createFormBuilder()
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe doivent être identique.',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => true,
            'first_options'  => ['label' => 'Mot de passe', 'attr' =>['placeholder'=>'Entrez votre mot de passe']],
            'second_options' => ['label' => 'Confirmer mot de passe','attr' =>['placeholder'=>'Confirmez votre mot de passe']],
            
        ])
        ->add('changer', SubmitType::class)
        ->getForm();
        $form->handleRequest($request); 
        
        if($form->isSubmitted() && $form->isValid()){
            $password= $form->getViewData($form); 
            $password=$password['password'];
            $repo = $this->getDoctrine()->getRepository(Verification::class);

            $validation= $repo->findOneBy(['token'=>$token]);
            if($validation != NULL){
                $id=$validation->getUserId();

                $repo = $this->getDoctrine()->getRepository(User::class);
                $user=$repo->find($id);
                $password= $encoder->encodePassword($user, $password); 
                $user->setPassword($password);
                $prenom = $user->getFirstName();
                $entityManager->persist($user);
                $entityManager->flush();
                $entityManager->remove($validation);
                $entityManager->flush();


                return $this->render('security/validation.html.twig', [
                'prenom' =>$prenom,
                ]);
            }

        else{
            return $this->render('security/error.html.twig');
        }
        
    }
    return $this->render('security/newmdp.html.twig', [
        'form'=>$form->createView()
    ]);        
}
}
