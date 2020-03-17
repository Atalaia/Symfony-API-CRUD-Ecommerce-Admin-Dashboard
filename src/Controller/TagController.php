<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\UtilService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="tags", methods={"GET"})
     */
    public function tagList(TagRepository $tagRepository, UtilService $utilService):Response
    {
        $tags = $tagRepository->findAll();
        return new Response($utilService->serializeJSON($tags));
    }

    /**
     * @Route("/{id}", name="tag_detail", methods={"GET"})
     */
    public function tagDetail(TagRepository $tagRepository, UtilService $utilService, $id):Response
    {
        $tag = $tagRepository->find($id);
        return new Response($utilService->serializeJSON($tag));
    }

    /**
     * @Route("/new", name="tag_new", methods={"POST"})
     */
    public function tagNew(Request $request):Response
    {
        $data = json_decode($request->getContent(), true);

        $tag = new Tag($data['name']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($tag);
        $entityManager->flush();

        return new Response('It worked. Believe me - I\'m an API');  
    }

    /**
     * @Route("/edit/{id}", name="tag_edit", methods={"PUT"})
     */
    public function tagEdit($id, TagRepository $tagRepository, Request $request):Response
    {
        $data= json_decode($request->getContent(), true);

        $tag = $tagRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$tag) {
            throw $this->createNotFoundException(
                'No tag found for id '.$id
            );
        }      
        $tag->setName($data['name']);
        $entityManager->flush();
        return $this->json("Tag updated");
    }

    /**
     * @Route("/delete/{id}", name="tag_delete", methods={"DELETE"})
     */
    public function delete(TagRepository $tagRepository, $id)
    {
        $tag = $tagRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($tag);
        $entityManager->flush();       
        return $this->json("Tag deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }

}
