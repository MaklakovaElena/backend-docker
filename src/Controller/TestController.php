<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route(path: '/test', name: 'test', methods: ['GET'])]
    public function list(): Response
    {
        return new Response('Welcome to Latte and Code ');
    }
}
