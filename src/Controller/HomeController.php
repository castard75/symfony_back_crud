<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Entity\User;

#[Route('/api')]
class HomeController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_get_user', methods: ['GET'])]
    public function app_get_user(User $user): JsonResponse
    {
        $serializer = new Serializer([new ObjectNormalizer()]);
        $jsonUser = $serializer->normalize($user, 'json', ['attributes' => ['id', 'username', 'email', 'imageUrl']]);
        return new JsonResponse($jsonUser, 200);
    }
}
