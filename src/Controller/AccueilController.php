<?php

namespace App\Controller;

use DateTimeZone;
use Stripe\Stripe; 
use App\Entity\Trajet;
use App\Entity\Article;
use App\Entity\Contact;
use App\Form\TrajetType;
use App\Form\ArticleType;
use App\Form\ContactType;
use App\Form\PaiementType;
use SMS\SMSPartnerAPI; 
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AccueilController extends AbstractController
{

    /**
     * @Route("/", name="accueil")
     */
    public function index (CommentaireRepository $repo , Request $request, SessionInterface $session) {
        // On créé un objet trajet
        $trajet = new Trajet();
        $timezone = new DateTimeZone('Europe/Paris');
        $trajet->setHour(new \DateTime( 'now', $timezone));
        // On crée le form builder
        $form = $this->createForm(TrajetType::class, $trajet);
        $commentaires= $repo->findLastCommentaire(); 
        dump($commentaires);  
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $cp1=$form->getViewData($form); 
            $cp2=$cp1;
            $cp1=$cp1->getCp1(); 
            $cp2=$cp2->getCp2();
            $cp1=$cp1[0].$cp1[1];
            $cp2=$cp2[0].$cp2[1]; 
           if(($cp1=='49' ||$cp1=='44'||$cp1=='85'||$cp1=='76') &&($cp2=='49' ||$cp2=='44'||$cp2=='85'||$cp2=='76')  ){
                $this->get('session')->set('trajet', $trajet);
                return $this->redirectToRoute('app_login');
            }
            else{
                return $this->render('accueil/devis.html.twig'); 
            }
        }

        return $this->render('accueil/index.html.twig', array(
            'form' => $form->createView(),
            'commentaires' => $commentaires, 
        ));
    }

    /**
     * @Route("/paiement", name="paiement")
    */
    public function paiement(SessionInterface $session, Request $request){


        $trajet= $session->get('trajet');  
        $prix = $trajet->getPrix();
        dump($trajet);
        $departP=$trajet->getDepart(); 
        $arriveP=$trajet->getArrive();
        dump($departP);
        
        $form = $this->get('form.factory')
        ->createNamedBuilder('payment-form')
        ->add('token', HiddenType::class, [
          'constraints' => [new NotBlank()],
        ])
        ->add('submit', SubmitType::class)
        ->getForm();
       
        if ($request->isMethod('POST')) {
          $form->handleRequest($request);
       
          if ($form->isValid()) {
            // TODO: charge the card
          }
        }
       

    return $this->render('accueil/paiement.html.twig', [
        'trajet'=>$trajet,
        'prix' => $prix,
        'form' =>$form->createView(),
        'depart'=>$departP,
        'arrive'=>$arriveP,
        'stripe_public_key' => $this->getParameter('stripe_public_key'), 
    ]);
} 

     /**
     * @Route("/commentaire", name="commentaire")
     */
    public function commentaire (Request $request,  EntityManagerInterface $entityManager) {
        // On créé un objet trajet
        $commentaire = new Commentaire();
        $timezone = new DateTimeZone('Europe/Paris');
        $form=$this->createForm(CommentaireType::class , $commentaire);
        $user=$this->getUser(); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setDate(new \DateTime( 'now', $timezone));
            $username= $user->getFirstname(); 
            $commentaire->setUsername($username); 
            $entityManager->persist($commentaire);
            $entityManager->flush();
            return $this->render('accueil/accueil.html.twig'); 
        }

        return $this->render('accueil/commentaire.html.twig', [
            'form' => $form->createView() , 
        ]);
    }


     /**
     * @Route("/contact", name="contact")
     */
    public function contact (Request $request,  EntityManagerInterface $entityManager,  \Swift_Mailer $mailer) {
        $contact = new Contact();
        $form=$this->createForm(ContactType::class , $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donnees = $form->getViewData($form); 
            $nom = $donnees->getNom(); 
            $email =  $donnees->getEmail(); 
            $corps =  $donnees->getMessage(); 
            $entityManager->persist($contact);
            $entityManager->flush();
            dump($nom); 
           $message = (new \Swift_Message('Hello Email'))
                ->setFrom('contact@taxischolet.fr')
                ->setTo($email)
                ->setBody(
                $this->renderView(
                    'email/contact.html.twig',
                    ['nom' => $nom,
                    'email'=>$email, 
                    'message'=>$corps,
                    ]
                ),
                'text/html'
             );
             $mailer->send($message); 
            //  return $this->render('accueil/accueil.html.twig'); 
        }

        return $this->render('accueil/contact.html.twig', [
            'form' => $form->createView(), 
        ]);
    }

     /**
     * @Route("/cree-article", name="newarticle")
     */
    public function newArticle (Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger){
        $article= new Article(); 
        $form=$this->createForm(ArticleType::class , $article);
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) {
            $timezone = new DateTimeZone('Europe/Paris');
            $article->setDate(new \DateTime( 'now', $timezone));
            $image=$form->get('image')->getData(); 
            if($image){
                $originalImageName = pathinfo($image->getClientOriginalName(),PATHINFO_FILENAME);
                $safeImageName = $slugger->slug($originalImageName); 
                $newImageName = $safeImageName.'-'.uniqid().'.'.$image->guessExtension();
                
                try{
                    $image->move(
                        $this->getParameter('image_directory'), 
                        $newImageName
                    ); 
                } catch(FileException $e){

                }
                $article->setImage_name($newImageName); 
            }
            
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('blog')); 
        }

        return $this->render('accueil/article.html.twig', [
            'form' => $form->createView(), 
        ]);
    }

     /**
     * @Route("/blog", name="blog")
     */
    public function blog()
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repo->findAll();
        $article=$articles[1];
        dump($article->getImage_name()); 
        return $this-> render('accueil/blog.html.twig', [
            'articles' => $articles, 
        ]); 
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show($id){
        $repo = $this->getDoctrine()->getRepository(Article::class); 
        $articles = $repo->findAll(); 
        $article= $repo->find($id); 
        $corps =  nl2br($article->getParagraphe());
        return $this->render('accueil/show.html.twig',[
            'articles' => $articles,
            'articleshow'=> $article,
            'corps' => $corps,  
        ]); 
    }

    /**
     * @Route("/mentions-légales", name="mention")
     */
    public function mention(){
        
        return $this->render('accueil/mentions.html.twig'); 
    }

     /**
     * @Route("/reservation", name="resa")
     */
    public function resa(Request $request,TrajetRepository $repo ){

        $user=$this->getUser();
        $id = $user->getId();
        $trajetsencours = $repo->TrajetenCour($id); 
        dump($trajetsencours); 
        
        return $this->render('accueil/trajetscours.html.twig',[
            'trajetsencours' =>$trajetsencours, 
        ]); 
    }
    
    /**
     * @Route("/supprimer/{idtrajet}", name="delete")
     */
    public function delete($idtrajet){

        $repo = $this->getDoctrine()->getRepository(Trajet::class); 
        $trajet=$repo->findOneBy(['id'=>$idtrajet]);
        $depart1= $trajet->getAdresse1()." ". $trajet->getCp1(); 
        $arrive1= $trajet->getAdresse2()." ". $trajet->getCp2(); 
        dump($trajet); 
        $em = $this->getDoctrine()->getManager();
        $em->remove($trajet);
        $em->flush();
        $user = $this->getUser();
        $nom=$user->getLastname(); 
        $prenom=$user->getFirstname(); 
        $smspartner = new SMSPartnerAPI (false);
        //check credits
        $result = $smspartner->checkCredits('?apiKey=6c30a24b177c04b53e4160a0ddde0ce091f9d66a');
        //send SMS
        $fields = array(
            "apiKey"=>"6c30a24b177c04b53e4160a0ddde0ce091f9d66a",
            "phoneNumbers"=>"0769849455",
            "message"=>"Un trajet de ".$depart1." à ".$arrive1." vien d'être annulé par monsieur ".$nom." ".$prenom.".",
            "sender" => "AMTaxi",
        );
        // $result = $smspartner->sendSms($fields);
        //get delivery
        // $result = $smspartner->checkStatusByNumber('?apiKey=6c30a24b177c04b53e4160a0ddde0ce091f9d66a&messageId=666&phoneNumber=0769849455'); 
        
        return $this->render('accueil/supptrajet.html.twig'); 
    }
}