<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/post')]
class PostController extends AbstractController
{
    #[Route('/add', name: 'app_add_post', methods: ['POST'])]
    public function add(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $description = $request->request->get('description');
        $picture = $request->files->get('picture');

        if (!$description || !$picture) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }
        $post = new Post();
        $user = $this->getUser();
        $now = new \DateTimeImmutable();
        try {
            $post->setDescription($description);
            $post->setImageFile($picture);
            $post->setImageName($post->getImageFile()->getFilename());
            $post->setCreatedBy($user);
            $post->setCreatedAt($now);
            $em->persist($post);
            $em->flush();
            $serializer = new Serializer([new ObjectNormalizer()]);
            $jsonPost = $serializer->normalize($post, 'json', ['attributes' => ['id', 'description', 'imageUrl', 'createdAt', 'createdBy' => ['id', 'username', 'imageUrl', 'email']]]);
            return new JsonResponse($jsonPost, 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/remove/{id}', name: 'app_remove_post', methods: ['DELETE'])]
    public function remove(EntityManagerInterface $em, Post $post): JsonResponse
    {
        if ($post->getCreatedBy() !== $this->getUser()) {
            return new JsonResponse(['error' => 'You are not allowed to delete this post'], 403);
        }
        $em->remove($post);
        $em->flush();
        return new JsonResponse(null, 204);
    }
}