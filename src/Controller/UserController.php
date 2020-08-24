<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use App\Services\JwtAuth;
use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController {

    //Metodo para Serializar una repsuesta
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

    //Metodo de prueba de API
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

    //Metodo para la creacion de usuarios
    public function createUser(Request $request) {
        $json = $request->get('json', null);
        $params = json_decode($json);

        if ($json != null) {

            $name = (!empty($params->name)) ? $params->name : null;
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname)) {

                $user = new User();

                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime('now'));

                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                //Compruebar si el usuario existe
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));

                if (count($isset_user) == 0) {
                    $em->persist($user); //persist() crea un objeto en el Entity Manager
                    $em->flush(); //flush() ejecuta las consultas en cola a la base de datos

                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Usuario registrado',
                        'user' => $user
                    ];
                } else {
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Usuario ya existe'
                    ];
                }
            } else {
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Datos Incorrectos'
                ];
            }
        }

        return new JsonResponse($data);
    }

    //Metodo para el login de usuario
    public function login(Request $request, JwtAuth $jwt_auth) {
        $json = $request->get('json', null);
        $params = json_decode($json);

        if ($json != null) {

            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $getToken = (!empty($params->getToken)) ? $params->getToken : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (!empty($email) && !empty($password) && count($validate_email) == 0) {


                $pwd = hash('sha256', $password);

                if ($getToken) {
                    $signup = $jwt_auth->signup($email, $pwd, $getToken);
                } else {
                    $signup = $jwt_auth->signup($email, $pwd);
                }

                return new JsonResponse($signup);
            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Datos no validos'
                ];
            }
        }

        return $this->resJson($data);
    }

    //Metodo para la actualizacion de datos de usuario
    public function updateUser(Request $request, JwtAuth $jwt_auth) {

        $token = $request->headers->get('Authorization');

        $auth_Check = $jwt_auth->checkToken($token);

        if ($auth_Check) {

            $em = $this->getDoctrine()->getManager();
            $identity = $jwt_auth->checkToken($token, true);

            $user_repo = $this->getDoctrine()->getRepository(User::class);
            $user = $user_repo->findOneBy([
                'id' => $identity->sub
            ]);

            $json = $request->get('json', null);
            $params = json_decode($json);

            if (!empty($json)) {

                $name = (!empty($params->name)) ? $params->name : null;
                $surname = (!empty($params->surname)) ? $params->surname : null;
                $email = (!empty($params->email)) ? $params->email : null;

                $validator = Validation::createValidator();
                $validate_email = $validator->validate($email, [
                    new Email()
                ]);

                if (!empty($email) && count($validate_email) == 0 && !empty($name) && !empty($surname)) {

                    $user->setName($name);
                    $user->setSurname($surname);
                    $user->setEmail($email);

                    $isset_user = $user_repo->findBy([
                        'email' => $email
                    ]);

                    if (count($isset_user) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();

                        $data = [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'El usuario ha sido actualizado',
                            'user' => $user
                        ];
                    } else {
                        $data = [
                            'code' => 400,
                            'status' => 'error',
                            'message' => 'No puedes usar este email'
                        ];
                    }
                } else {
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'Los datos no son correctos'
                    ];
                }
            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Los campos son requeridos'
                ];
            }
        }

        return $this->resJson($data);
    }

}
