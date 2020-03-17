<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\UtilService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="categories", methods={"GET"})
     */
    public function categoryList(CategoryRepository $categoryRepository, UtilService $utilService):Response
    {
        $categories = $categoryRepository->findAll();
        return new Response($utilService->serializeJSON($categories));
    }

    /**
     * @Route("/{id}", name="category_detail", methods={"GET"})
     */
    public function categoryDetail(CategoryRepository $categoryRepository, UtilService $utilService, $id):Response
    {
        $category = $categoryRepository->find($id);
        return new Response($utilService->serializeJSON($category));
    }

    /**
     * @Route("/new", name="category_new", methods={"POST"})
     */
    public function categoryNew(Request $request):Response
    {
        $data = json_decode($request->getContent(), true);

        $category = new Category($data['name']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        return new Response('It worked. Believe me - I\'m an API');  
    }

    /**
     * @Route("/edit/{id}", name="category_edit", methods={"PUT"})
     */
    public function categoryEdit($id, CategoryRepository $categoryRepository, Request $request):Response
    {
        $data= json_decode($request->getContent(), true);

        $category = $categoryRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }      
        $category->setName($data['name']);
        $entityManager->flush();
        return $this->json("Category updated");
    }

    /**
     * @Route("/delete/{id}", name="category_delete", methods={"DELETE"})
     */
    public function delete(CategoryRepository $categoryRepository, $id)
    {
        $category = $categoryRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();       
        return $this->json("Category deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }

}
