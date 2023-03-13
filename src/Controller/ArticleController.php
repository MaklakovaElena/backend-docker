<?php

namespace App\Controller;

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Article;  
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;



/**
 * @Route("/api", name="api_")
 */

class ArticleController extends AbstractController
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
    * @Route("/article", name="articles", methods={"GET"})
    */
    public function index(ManagerRegistry $doctrine,  Request $request): Response
    {
        // $oauth = $this->getParameter('oauth');
        $auth_header = $request->headers->get('Authorization');
        $data = [];
        $headers =['headers' => [
            "Authorization: $auth_header",
        ]];
        $url = 'http://192.168.88.216:8080/me';
        $url_users = 'http://192.168.88.216:8080/users';
        $response = $this->client->request('GET', $url, $headers);
        if (200 !== $response->getStatusCode()) {
            return $this->json($response->getStatusCode());
        };
        
        $articles = $doctrine
            ->getRepository(Article::class)
            ->findBy(array('deleted_at'=>null));
 
        foreach ($articles as $article) {
            $data[] = [
               'id' => $article->getId(),
               'title' => $article->getTitle(),
               'text' => $article->getText(),
               'created_at' => $article->getCreatedAt(),
               'updated_at' => $article->getUpdatedAt(),
               'user' => $article->getUserId(),
            ];
        }
    return $this->json($oauth);
    }

    /**
     * @Route("/article", name="article_new", methods={"POST"})
     */
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $auth_header = $request->headers->get('Authorization');
        $url = 'http://192.168.88.216:8080/me';
        $response = $this->client->request('GET', $url, [
            'headers' => [
                "Authorization: $auth_header",
            ]
        ] );
        if (200 !== $response->getStatusCode()) {
            return $this->json($response->getStatusCode());
        };

        $requestAsArray = [];
        if ($content = $request->getContent()) {
            $requestAsArray = json_decode($content, true);
        };
        $user_info = $response -> toArray();

        $entityManager = $doctrine->getManager();
  
        $article = new Article();
        $article->setTitle($requestAsArray['title']);
        $article->setText($requestAsArray['text']);
        $article->setCreatedAt(new \DateTimeImmutable('now'));
        $article->setUserId($user_info['id']);
  
        $entityManager->persist($article);
        $entityManager->flush();

        return $this->json('Created new article successfully with id ' . $article->getId());
    }

    /**
     * @Route("/article/{id}", name="article_get", methods={"GET"})
     */
    public function show(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $auth_header = $request->headers->get('Authorization');
        $headers =['headers' => [
            "Authorization: $auth_header",
        ]];
        $url = 'http://192.168.88.216:8080/me';
        $url_users = 'http://192.168.88.216:8080/users';
        $response = $this->client->request('GET', $url, $headers);
        if (200 !== $response->getStatusCode()) {
            return $this->json($response->getStatusCode());
        };
        $article = $doctrine->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->json('No article found for id' . $id, 404);
        }
        $data =  [
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'text' => $article->getText(),
            'created_at' => $article->getCreatedAt(),
            'updated_at' => $article->getUpdatedAt(),
            'user_id' => $article->getUserId(),
        ];
        return $this->json($data);
    }
    /**
     * @Route("/article/{id}", name="article_update", methods={"PUT"})
     */
    public function update(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $auth_header = $request->headers->get('Authorization');
        $headers =['headers' => [
            "Authorization: $auth_header",
        ]];
        $url = 'http://192.168.88.216:8080/me';
        $response = $this->client->request('GET', $url, $headers);
        if (200 !== $response->getStatusCode()) {
            return $this->json($response->getStatusCode());
        };
        $user_info = $response -> toArray();
        $entityManager = $doctrine->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->json('No article found for id' . $id, 404);
        };
        // if ($article->getUserId() !== $user_info['id']) {
        //     return $this->json('It is not  your article' . $id, 403);
        // };
        $requestAsArray = [];
        if ($content = $request->getContent()) {
            $requestAsArray = json_decode($content, true);
        };
        $article->setTitle($requestAsArray['title']);
        $article->setText($requestAsArray['text']);
        $article->setUpdatedAt(new \DateTime('now'));
        $entityManager->flush();
        $data =  [
            'id' => $article->getId(),
            'name' => $article->getTitle(),
            'description' => $article->getText(),
        ];
        return $this->json($data);
    }
    /**
     * @Route("/article/{id}", name="article_delete", methods={"DELETE"})
     */
    public function delete(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $auth_header = $request->headers->get('Authorization');
        $url = 'http://192.168.88.216:8080/me';
        $response = $this->client->request('GET', $url, [
            'headers' => [
                "Authorization: $auth_header",
            ]
        ] );
        if (200 !== $response->getStatusCode()) {
            return $this->json($response->getStatusCode());
        };
        $user_info = $response->toArray();
        $entityManager = $doctrine->getManager();
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->json('No article found for id' . $id, 404);
        }
        // if ($article->getUserId() !== $user_info['id']) {
        //     return $this->json('It is not  your article' . $id, 403);
        // };
        // $entityManager->remove($article);
        // $entityManager->flush();
        $article->setDeletedAt(new \DateTimeImmutable('now'));
        $entityManager->flush();
        return $this->json('Deleted a article successfully with id ' . $id);
    }
}
