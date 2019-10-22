<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $postRepository;
    private $entityManager;


    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="post.list")
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
    public function show(Post $post)
    {
        return $this->render('post/show.html.twig', [
            "post" => $post
        ]);
    }

    /**
     * @Route("profile/post/add", name="post.add")
     */
    public function add(Request $request)
    {

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file =  $form->get('image')->getData();
            if (!is_null($file)) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_dir'), $filename);
                $post->setImage($filename);
            } else {
                $post->setImage('');
            }

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->redirectToRoute('post.list');
        }

        return $this->render('post/edit.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("profile/post/edit/{id}", name="post.edit")
     */
    public function edit(Request $request, Post $post)
    {

        $lastImage = $post->getImage();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file =  $form->get('image')->getData();
            if (!is_null($file)) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_dir'), $filename);
                $post->setImage($filename);
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
        ]);
    }

    /**
     * @Route("profile/post/delete/{id}", name = "post.delete", methods="DELETE")
     */
    public function delete(Post $post, Request $request)
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
