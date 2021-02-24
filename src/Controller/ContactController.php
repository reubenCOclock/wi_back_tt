<?php
 namespace App\Controller; 

 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\HttpFoundation\Response;
 use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
 use Symfony\Component\Serializer\Encoder\JsonEncoder;
 use App\Entity\Contact;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\Routing\Annotation\Route;
 use Doctrine\Common\Persistence\ObjectManager;
 use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

 use Symfony\Component\HttpFoundation\JsonResponse;

 use Symfony\Component\Serializer\SerializerInterface;


use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


 class ContactController extends AbstractController{

  
     /**
     * @Route("/api/member/register",methods={"POST"})
     */
    public function insertNewContact(Request $request){
        try{
            $em=$this->getDoctrine()->getManager();
        //recuperation du request body
        $requestBody=json_decode($request->getContent(),true);
        
        $contactRepository=$em->getRepository(Contact::class);
        //initialisation d'un nouveau contact
        $newContact=new Contact();

        
        //en bouclant sur les valuers du request body, les setters sont appeler dynamiquement
         foreach($requestBody as $key=>$value){
             $method='set'.ucFirst($key);
             $newContact->$method($value);
                  
         }
         $em->persist($newContact);
         
         $em->flush();

         return $this->json(["message"=>"merci l'utilisateur bien ajouté en bdd"]);

        }

        catch(\Exception $e){
            return new Response("error error error");
        }
        
        
     } 
     /**
      * @Route("/api/member/findByEmail",METHODS={"POST"})
      */
     
     public function findContactByEmail(Request $request){
         try{
            $em=$this->getDoctrine()->getManager();

            $contactRepository=$em->getRepository(Contact::class);
            $requestBody=json_decode($request->getContent(),true);
   
            $findContact=$contactRepository->findOneBy(["email"=>$requestBody["email"]]);
   
            if($findContact){
               $encoders = [new XmlEncoder(), new JsonEncoder()];
               $normalizers = [new ObjectNormalizer()];
   
               $serializer = new Serializer($normalizers, $encoders);
   
               $jsonContact=$serializer->serialize($findContact,"json");
   
               return new Response($jsonContact);
            }
   
            else{
                return new JsonResponse(["message"=>"aucun contact trouvé"]);
            }

         }

         catch(\Exception $e){
             return new Response("errore errore errore".$e->getMessage());
         }
        
     }

     /**
      * @Route("/api/modify_contact/{id}",METHODS={"POST"})
      */

     public function modifyContactInformation(Request $request,$id){

        try{

           

            $em=$this->getDoctrine()->getManager();

        $contactRepository=$em->getRepository(Contact::class);

        $getContact=$contactRepository->findOneBy(["id"=>$id]);
        
        

        $requestBody=json_decode($request->getContent(),true);

      
        
        foreach($requestBody as $key=>$value){
            
            $method='set'.ucFirst($key);
            
             $getContact->$method($value);
            
            
        }
       
        $em->persist($getContact);
        $em->flush();

        return new JsonResponse(["message"=>"contact mise a jour"]); 
        }

        catch(\Excepetion $e){
            return new Response("error error error" .$e->getMessage());
        }
        

        
     }

     
 }
 







?>