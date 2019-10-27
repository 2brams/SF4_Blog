<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\File;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $postRepository;
    private $userRepository;
    private $entityManager;


    public function __construct(PostRepository $postRepository, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/profile/post", name="post.list")
     */
    public function index()
    {
        $posts = $this->postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/show/{id}", name="post.show")
     */
    public function show(Request $request, Post $post)
    {
        $lastPost = $this->postRepository->findBy([], ['createdAt' => 'DESC'], 3);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setPost($this->postRepository->find($request->get('post')));
            $comment->setUser($this->userRepository->find($request->get('user')));

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirect($request->getUri());
        }
        return $this->render('post/show.html.twig', [
            "post" => $post,
            "lastPost" => $lastPost,
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/post/add", name="post.add")
     */
    public function add(Request $request)
    {

        $post = new Post();
        $image = new File();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file =  $form->get('image')->getData();
            if (!is_null($file)) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_dir'), $filename);
                $image->setName($filename);
                $post->setImage($image);
            }

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->redirectToRoute('post.list');
        }

        return $this->render('post/edit.html.twig', [
            "form" => $form->createView(),
            "action" => "Add",
        ]);
    }

    /**
     * @Route("/profile/post/edit/{id}", name="post.edit")
     */
    public function edit(Request $request, Post $post)
    {
        $image = new File();
        $lastImage = $post->getImage();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file =  $form->get('image')->getData();
            if (!is_null($file)) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_dir'), $filename);
                $image->setName($filename);
                $post->setImage($image);
                if ($lastImage !== '') {
                    unlink($this->getParameter('uploads_dir') . $lastImage);
                }
            } else {
                $post->setImage($lastImage);
            }

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->redirectToRoute('post.list');
        }

        return $this->render('post/edit.html.twig', [
            "form" => $form->createView(),
            "post" => $post,
            "action" => "Edit",
        ]);
    }

    /**
     * @Route("/profile/post/delete/{id}", name = "post.delete", methods="DELETE")
     */
    public function deletePost(Post $post, Request $request)
    {
        $image = $post->getImage();
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->get('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();
            unlink($this->getParameter('uploads_dir') . $image);
        }
        return $this->redirectToRoute('post.list');
    }

    /**
     * @Route("/profile/comment/delete/{id}", name = "comment.delete", methods="DELETE")
     */
    public function deleteComment(Comment $comment, Request $request)
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->get('_token'))) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('post.show', ['id' => $request->get('post')]);
    }

    /**
     * @Route("/search", name = "post.search")
     */
    public function search(Request $request)
    {

        $requestString = $request->get('data');
        $posts =  $this->postRepository->findEntitiesByString($requestString);
        if (!$posts) {
            $result['posts']['error'] = "Post Not found :( ";
        } else {
            $result['posts'] = $this->getRealEntities($posts);
        }
        return new Response(json_encode($result));
    }
    private function getRealEntities($posts)
    {
        foreach ($posts as $post) {
            $realEntities[$post->getId()] = [$post->getImage(), $post->getTitle()];
        }
        return $realEntities;
    }
}
