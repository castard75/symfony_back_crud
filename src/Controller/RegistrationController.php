<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'app_registration',methods:['POST'])]
    public function index(Request $request,UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): JsonResponse
    {
        $user = new User();

        try {
              $email = $request->request->get('email');
              $username = $request->request->get('username');
              $password = $request->request->get('password');
   
            if($email == null || !filter_var($email,FILTER_VALIDATE_EMAIL)){

                throw new \Exception('eRReuR email ');
            }
            // if($password == null || !filter_var($password,FILTER_VALIDATE_REGEXP),){

            //     throw new \Exception('eRReuR email ');
            // }
            $user->setEmail($email);
            $user->setUsername($username);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            return new JsonResponse(['message' => 'user created'],201);

        } catch(\Exception $e){

            return new JsonResponse(['message' => $e->getMessage()],400);
        }

      
    }
}
