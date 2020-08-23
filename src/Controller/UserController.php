<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController {

    private function resJson($data) {
        //Serializar datos con servicio Serializer
        $json = $this->get('serializer')->serialize($data, 'json');

        //Resonse con Http Fundation
        $response = new Response();

        //Asignar contenido a la respuesta
        $response->setContent($json);

        //Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');
        
        //Devolver la respuesta
        return $response;
    }

    public function index() {

        //Se realiza como un tipo de instancia de las clases
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        $users = $user_repo->findAll();
        $user = $user_repo->findAll();
        /*
          foreach ($users as $user){
          echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";

          foreach ($user->getVideos() as $video){
          echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
          }
          }
          die();
         */

        return $this->resJson($user);
    }

}
