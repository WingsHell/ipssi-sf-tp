<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Include paginator interface

/**
 * Class BlogController
 * @package App\Controller
 * @Route(path="/",name="blog")
 */
class BlogController extends AbstractController
{
    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {


        $em = $this->getDoctrine()->getManager();

        /** @var ArticleRepository $repository */
        $repository = $em->getRepository(Article::class);

        $articles = $repository->createQueryBuilder('a')
            ->orderBy('a.creationDate', 'DESC')
            ->getQuery();


        $articles = $paginator->paginate($articles, $request->query->getInt('page', 1), 10);


        return $this->render('Blog/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route(path="/show/{id}")
     */
    public function show(Request $request, int $id): Response
    {
        /** @var ArticleRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find($id);

        if ($article === null) {
            throw $this->createNotFoundException();
        }

        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRepository->findByArticleId($id);
        $commentForm = $this->createForm(CommentFormType::class);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            dd($comments);
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentForm->getData());
            $em->flush();
        }
        return $this->render('ArticleController/view.html.twig', [
            'article' => $article,
            'comments' => $comments,
        ]);
    }
}
