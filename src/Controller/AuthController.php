<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    #[Route('api/registration', name: 'create_user',methods:['POST'])]
    public function createUser(Request $request,ObjectManager $manager,SerializerInterface $serializer,UserPasswordHasherInterface $hasher): JsonResponse
    {

        $user=$serializer->deserialize($request->getContent(),User::class,'json');
        $pass=$hasher->hashPassword($user,$user->getPassword());
        $user->setPassword($pass);
        $manager->persist($user);
        $manager->flush();
        $userJson=$serializer->serialize($user,'json');
        return new JsonResponse($userJson,Response::HTTP_CREATED,[],true);
        
    }
    #[Route('api/login/{email}/{password}',name:'get_user',methods:['GET'])]
    public function searchUser(User $user,SerializerInterface $serializer,UserPasswordHasherInterface $hasher): JsonResponse
    {
        /*$user=new User();
        $passHash=$hasher->hashPassword($user,$password);
        $user=$ur->findOneBy(['email'=>$email,'password'=>$passHash]);*/
        if($user){
            $userJson=$serializer->serialize($user,'json',['groups'=>'getQ']);
            return new JsonResponse($userJson,Response::HTTP_OK,[],true);
        }
    }
}