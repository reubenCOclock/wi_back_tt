<?php
 namespace App\Controller; 

 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\HttpFoundation\Response;

 use Symfony\Component\Serializer\Encoder\JsonEncoder;
 use App\Entity\Contact;
 use App\Entity\Admin;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\Routing\Annotation\Route;

 use Doctrine\Common\Persistence\ObjectManager;
 use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
 use Symfony\Component\HttpFoundation\JsonResponse;




use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

 
 //use Symfony\Component\Serializer\Encoder\XmlEncoder;
 //use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
 //use Symfony\Component\Serializer\Serializer;

 class AdminController extends AbstractController{

  // ici je crée une route pour le login d'un admin, mon choix de separer les admins et les contacts au lieu de creer une table User vient surtout du fait que les "Contacts" n'ont pas de pouvoir de connextion ni d'inscription pour pouvoir ensuite se connecter donc ils sont pas vraiment des utilisateurs. Aussi, étant donée que je insere directement un admin dans la bdd, j'ai attribué un champs is_hashed pour verifier lors d'une connexion si son mdp a bien été haché ou non 
     /**
     * @Route("/api/admin_login",methods={"POST"})
     */
     
     function adminLogin(Request $request){

        
        
        try{
          
            $em=$this->getDoctrine()->getManager();

           
        $adminRepository=$em->getRepository(Admin::class);
        $admins=$adminRepository->findAll();
        $requestBody=json_decode($request->getContent(),true);
         
        
         $findAdmin=$adminRepository->findOneBy(["email"=>$requestBody["email"]]);
          
         if($findAdmin){
            
             if(!$findAdmin->getIsHashed()){
                 $findAdmin->setIsHashed(true);
                 $findAdmin->setPassword(password_hash($requestBody["password"],PASSWORD_ARGON2I));
                 $em->persist($findAdmin);
                 $em->flush();
             }

             

             
             if(password_verify($requestBody["password"],$findAdmin->getPassword())){
               
                 return new JsonResponse(["message"=>"user found","token"=>$findAdmin->getToken()],200);
             }

             else{
                 return new JsonResponse(["message"=>"bad credentials"]);
             }
         }

         else{
             return new JsonResponse(["message"=>"bad credentials"]);
         }
        }

        catch(\Exception $e){

            echo "an error has occured".$e->getMessage();
            return new Response("error error error");
        }
        
     } 

     /**
      * @Route("/api/admin/getContacts",methods={"GET"})
      */
     function getAllContactInfo(Request $request){
        $em=$this->getDoctrine()->getManager();

       $contactRepository=$em->getRepository(Contact::class);

       $isAuthorized=false;
        if($request->headers->has("Authorization")){
           
            $authorizationHeader = $request->headers->get('Authorization');
            $token=substr($authorizationHeader,7);

            $adminRepository=$em->getRepository(Admin::class);

            $findAdmin=$adminRepository->findOneBy(["token"=>$token]);

            if($findAdmin){
                $isAuthorized=true;
            }

        }

        if($isAuthorized==true){
            $contacts=$contactRepository->findAll();

            $encoders = [new XmlEncoder(), new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];

            $serializer = new Serializer($normalizers, $encoders);

            $jsonContacts=$serializer->serialize($contacts,"json");
            /*
            $jsonContacts=[];
        foreach($contacts as $index=>$contact){
            $jsonContacts[$index]["id"]=$contact->getId();
            $jsonContacts[$index]["firstname"]=$contact->getFirstname();
            $jsonContacts[$index]["lastname"]=$contact->getLastname();
            $jsonContacts[$index]["email"]=$contact->getEmail();
            $jsonContacts[$index]["telephone"]=$contact->getTelephone();
            $jsonContacts[$index]["zipcode"]=$contact->getZipcode();
            $jsonContacts[$index]["city"]=$contact->getCity();
            $jsonContacts[$index]["comment"]=$contact->getComment();
            $jsonContacts[$index]["address"]=$contact->getAddress();
        
        }
        */
        return new Response($jsonContacts);
            
        }

        else{
           return new JsonResponse(["message"=>"unauthorized"],401);
        }
        
     }

     /**
      * @Route("/api/admin/contactInfo/{id}",methods={"GET"})
      */

     public function selectContactInformation(Request $request,$id){
        $em=$this->getDoctrine()->getManager();
        try{
            if($request->headers->has("Authorization")){
           
                $authorizationHeader = $request->headers->get('Authorization');
                $token=substr($authorizationHeader,7);
    
                $adminRepository=$em->getRepository(Admin::class);
    
                $findAdmin=$adminRepository->findOneBy(["token"=>$token]);
    
                if($findAdmin){
                    $isAuthorized=true;
                }
    
            } 
            if($isAuthorized==true){
                $em=$this->getDoctrine()->getManager();
                $contactRepository=$em->getRepository(Contact::class);
       
                $selectedContact=$contactRepository->findBy(["id"=>$id]);
       
                $encoders = [new XmlEncoder(), new JsonEncoder()];
                   $normalizers = [new ObjectNormalizer()];
       
               $serializer = new Serializer($normalizers, $encoders);
       
                $jsonContact=$serializer->serialize($selectedContact,"json");
       
                return new Response($jsonContact);
            }
    
            else{
                return new Response("error error error");
            }
        }

        catch(\Exception $e){
            return new Response("error error error". $e->getMessage());
        }
      
        
            
        }

       
        
     

     /**
      * @Route("/api/admin/updateContact/{id}",methods={"POST"})
      */
     public function updateContactField(Request $request,$id){

        try{
            $em=$this->getDoctrine()->getManager();
        
        $contactRepository=$em->getRepository(Contact::class);

        $findContact=$contactRepository->findOneBy(["id"=>$id]);
         echo $findContact->getFirstName();
        $requestBody=json_decode($request->getContent(),true);

        foreach($requestBody as $key=>$value){
            $method='set'.ucFirst($key);

            $findContact->$method($value);

            
        }
        echo "we have made it up to here";
        
        $em->persist($findContact);

        $em->flush();

        return new Response("en cours de mise a jour");
        }

        catch(\Exception $e){

            return new Response("error error error". $e->getMessage());
        }
        


     }
     /**
      * @Route("api/admin/contact/delete/{id}",methods={"GET"})
      */

     public function deleteContact($id){
        $em=$this->getDoctrine()->getManager();
        $contactRepository=$em->getRepository(Contact::class);

        $contact=$contactRepository->findOneBy(["id"=>$id]);

        $em->remove($contact);
        $em->flush();

        return New Response("Contact Supprimé");
     }
    

     
 }
 
