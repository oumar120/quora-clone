<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Reponse;
use App\Entity\Question;
use App\Entity\Commentaire;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use App\Repository\ReponseRepository;
use App\Repository\QuestionRepository;
use Doctrine\Persistence\ObjectManager;
use App\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DataController extends AbstractController
{
    #[Route('api/setdata', name:'create_question',methods:['POST'])]
    public function createQestion(SerializerInterface $serializer,Request $request,ObjectManager $manager,UserRepository $ur): JsonResponse
    {
            $question=$serializer->deserialize($request->getContent(),Question::class,'json');
            $content=$request->toArray();
            $idUser=$content['id'];
            $question->setUser($ur->find($idUser));
            $manager->persist($question);
            $manager->flush();
            $questionJson=$serializer->serialize($question,'json',['groups'=>"getQ"]);
            return new jsonResponse($questionJson,Response::HTTP_CREATED,[],true);
            
    }
    #[Route('api/setreponse', name:'create_reponse',methods:['POST'])]
    public function createReponse(SerializerInterface $serializer,Request $request,ObjectManager $manager,UserRepository $ur,QuestionRepository $qr): JsonResponse
    {
            $uploadedFile=$request->files->get('image');
            $idUser=$request->request->get('id');
            $idQuestion=$request->request->get('id_question');
            $content=$request->request->get('content');
            $reponse=new Reponse;
            $reponse->setContent($content);
            $reponse->setQuestion($qr->find($idQuestion));
            $reponse->setUser($ur->find($idUser));
            if($uploadedFile){
                $fileName =  uniqid(). '.' .$uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('images_directory'), // Le dossier dans lequel le fichier va être charger
                        $fileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $reponse->setImage($fileName);
            }
            $manager->persist($reponse);
            $manager->flush();
            $reponseJson=$serializer->serialize($reponse,'json',['groups'=>"getR"]);
            return new jsonResponse($reponseJson,Response::HTTP_CREATED,[],true);
    }
    #[Route('api/setC', name:'create_commentaire',methods:['POST'])]
    public function createCommentaire(SerializerInterface $serializer,Request $request,ObjectManager $manager,UserRepository $ur,ReponseRepository $rr): JsonResponse
    {
            $comment=$serializer->deserialize($request->getContent(),Commentaire::class,'json');
            $content=$request->toArray();
            $idUser=$content['user_id'];
            $idReponse=$content['reponse_id'];
            $comment->setUser($ur->find($idUser));
            $comment->setReponse($rr->find($idReponse));
            $manager->persist($comment);
            $manager->flush();
            $questionJson=$serializer->serialize($comment,'json',['groups'=>"getC"]);
            return new jsonResponse($questionJson,Response::HTTP_CREATED,[],true);
            
    }
    #[Route('api/setvote', name:'create_vote',methods:['POST'])]
    public function createVote(SerializerInterface $serializer,Request $request,ObjectManager $manager,UserRepository $ur,ReponseRepository $rr): JsonResponse
    {
            $vote=$serializer->deserialize($request->getContent(),Vote::class,'json');
            $content=$request->toArray();
            $idReponse=$content['id_reponse'];
            $idUser=$content['id_user'];
            $vote->setReponse($rr->find($idReponse));
            $vote->setUser($ur->find($idUser));
            $manager->persist($vote);
            $manager->flush();
            $voteJson=$serializer->serialize($vote,'json');
            return new jsonResponse($voteJson,Response::HTTP_CREATED,[],true);
            
    }
    
    #[Route('api/getQ', name:'get_question',methods:['GET'])]
    public function getQestion(SerializerInterface $serializer,Request $request,QuestionRepository $qr): JsonResponse
    {
            $question=$qr->getQuestion();
            $questionJson=$serializer->serialize($question,'json',['groups'=>"getQ"]);
            return new jsonResponse($questionJson,Response::HTTP_OK,[],true);
            
    }
    #[Route('api/getR', name:'get_reponse',methods:['GET'])]
    public function getReponse(SerializerInterface $serializer,Request $request,ReponseRepository $qr): JsonResponse
    {
            $reponse=$qr->getReponse();
            $reponseJson=$serializer->serialize($reponse,'json',['groups'=>"getR"]);
            return new jsonResponse($reponseJson,Response::HTTP_OK,[],true);
            
    }
    #[Route('api/getC/{id}', name:'get_commentaire',methods:['GET'])]
    public function getCommentaire($id,SerializerInterface $serializer,Request $request,CommentaireRepository $cr): JsonResponse
    {
            $comment=$cr->findByReponse($id);
            $questionJson=$serializer->serialize($comment,'json',['groups'=>"getC"]);
            return new jsonResponse($questionJson,Response::HTTP_OK,[],true);
            
    }
    #[Route('api/getV/{id_user}/{id_reponse}', name:'get_vote',methods:['GET'])]
    public function getVote($id_user,$id_reponse,SerializerInterface $serializer,Request $request,VoteRepository $vr): JsonResponse
    {
            $vote=$vr->findOneBy([
                'user'=>$id_user,
                'reponse'=>$id_reponse
            ]);
            $vJson=$serializer->serialize($vote,'json',['groups'=>"getV"]);
            return new jsonResponse($vJson,Response::HTTP_OK,[],true);
            
    }

}
