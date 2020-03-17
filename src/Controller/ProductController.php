<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\TagRepository;
use App\Service\UtilService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="products", methods={"GET"})
     */
    public function productList(ProductRepository $productRepository, UtilService $utilService):Response
    {
        $products = $productRepository->findAll();
        return new Response($utilService->serializeJSON($products));
    }

    /**
     * @Route("/{id}", name="product_detail", methods={"GET"})
     */
    public function productDetail(ProductRepository $productRepository, UtilService $utilService, $id):Response
    {
        $product = $productRepository->find($id);
        return new Response($utilService->serializeJSON($product));
    }

    /**
     * @Route("/new", name="product_new", methods={"POST"})
     */
    public function add(Request $request, CategoryRepository $categoryRepository, TagRepository $tagRepository)
    {
        $data = json_decode($request->getContent(), true);
        $category = $categoryRepository->find($data['category']);
        $product = new product($data['name'],$data['price'],$data['photo'],$category);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);


        $entityManager->flush();
        foreach($data['tag'] as $tag){
            $tag = $tagRepository->find($tag);
            $product->addTag($tag); 
            $entityManager->persist($product);
        }
        $entityManager->flush();

        return $this->json($data);
    }

    /**
     * @Route("/edit/{id}", name="product_edit", methods={"PUT"})
     */
    public function productEdit($id, ProductRepository $productRepository, 
    CategoryRepository $categoryRepository, TagRepository $tagRepository, Request $request):Response
    {
        $data= json_decode($request->getContent(), true);

        $categoryId = $categoryRepository->find($data['category']);

        $product = $productRepository->find($id);

        $currentTags = $product->getTag();

        foreach($currentTags as $currentTag) {
            $product->removeTag($currentTag);
        }

        $entityManager = $this->getDoctrine()->getManager();
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }      
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setPhoto($data['photo']);
        $product->setCategory($categoryId);

        foreach($data['tag'] as $tag) {
            $tag = $tagRepository->find($tag);
            $product->addTag($tag);
        }

        $entityManager->flush();
        return $this->json("Product updated");
    }

    /**
     * @Route("/delete/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(ProductRepository $productRepository, $id)
    {
        $product = $productRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($product);
        $entityManager->flush();       
        return $this->json("Product deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }

}
