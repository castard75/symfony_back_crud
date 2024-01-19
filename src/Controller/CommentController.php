<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/comment')]
class CommentController extends AbstractController
{
    #[Route('/add/{id}', name: 'app_add_comment', methods: ['POST'])]
    public function add(EntityManagerInterface $em, Request $request, Post $post): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['content'])) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }
        $content = $data['content'];


        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        // TODO CHECK IF USER IS OWNER OR IF USER IS A FOLLOWER OF THE OWNER (POST CREATOR)

        $user = $this->getUser();
        $now = new \DateTimeImmutable();
        $comment = new Comment();

        try {
            $comment->setContent($content);
            $comment->setCreatedAt($now);
            $comment->setUser($user);
            $comment->setPost($post);
            $em->persist($comment);
            $em->flush();
            $serializer = new Serializer([new ObjectNormalizer()]);
            $jsonComment = $serializer->normalize($comment, 'json', ['attributes' => ['id', 'content', 'createdAt', 'user' => ['id', 'username', 'imageUrl', 'email']]]);
            return new JsonResponse($jsonComment, 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/remove/{id}', name: 'app_remove_comment', methods: ['DELETE'])]
    public function remove(EntityManagerInterface $em, Comment $comment): JsonResponse
    {
        if ($comment->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'You are not allowed to delete this comment'], 403);
        }
        $em->remove($comment);
        $em->flush();
        return new JsonResponse(null, 204);
    }

    #[Route('/edit/{id}', name: 'app_update_comment', methods: ['PUT'])]
    public function update(EntityManagerInterface $em, Request $request, Comment $comment): JsonResponse
    {
        if ($comment->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'You are not allowed to edit this comment'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $content = $data['content'];

        if (!$content) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        if (!$comment) {
            return new JsonResponse(['error' => 'Comment not found'], 404);
        }

        try {
            $comment->setContent($content);
            $em->persist($comment);
            $em->flush();
            $serializer = new Serializer([new ObjectNormalizer()]);
            $jsonComment = $serializer->normalize($comment, 'json', ['attributes' => ['id', 'content', 'createdAt', 'user' => ['id', 'username', 'imageUrl', 'email']]]);
            return new JsonResponse($jsonComment, 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}