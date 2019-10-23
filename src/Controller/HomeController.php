<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $postRepository;


    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $posts = $this->postRepository->findAll();

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
