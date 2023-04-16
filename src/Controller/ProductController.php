<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }
    #[Route('/products', name: 'app_product')]
    #[Route('/', name: 'app_product')]
    public function show(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $products = $this->productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/product/delete/{id}', name: 'app_product_delete', defaults: ['id' => null] )]
    public function delete($id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->find(['id' => $id]);
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        /**
         * @Route("/")
         */


        return $this->redirect('/products');
    }

    #[Route('/product/add', name: 'app_product_add', methods: ['GET', 'HEAD'] )]
    public function add(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('product/add.html.twig');
    }

    #[Route('/product/add', name: 'app_product_add_post', methods: ['POST'] )]
    public function addProduct(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $title = (string)$request->get('title');
        $description = (string)$request->get('description');
        $priceEur = (float)$request->get('priceEur');

        $product = new Product();

        $product->setTitle($title);
        $product->setDescription($description);
        $product->setPriceEur($priceEur);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        if ($product->getId()) {
            return $this->redirect('/products');
        }

        return new Response('Failed to create a new product.', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit', defaults: ['id' => null], methods: ['GET', 'HEAD'])]
    public function edit($id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->find(['id' => $id]);
        return $this->render('product/edit.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit_post', defaults: ['id' => null], methods: ['POST'])]
    public function editPost($id, Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $title = (string)$request->get('title');
        $description = (string)$request->get('description');
        $priceEur = (float)$request->get('priceEur');

        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->find(['id' => $id]);

        if ($product) {
            $product->setTitle($title);
            $product->setDescription($description);
            $product->setPriceEur($priceEur);

            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }

        return $this->redirect('/products');
    }
}
