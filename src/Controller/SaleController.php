<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Sale;
use App\Repository\SaleRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use App\Service\UtilService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sale")
 */
class SaleController extends AbstractController
{
    /**
     * @Route("/", name="sales", methods={"GET"})
     */
    public function saleList(SaleRepository $saleRepository, UtilService $utilService):Response
    {
        $sales = $saleRepository->findAll();
        return new Response($utilService->serializeJSON($sales));
    }

    /**
     * @Route("/{id}", name="sale_detail", methods={"GET"})
     */
    public function saleDetail(SaleRepository $saleRepository, UtilService $utilService, $id):Response
    {
        $sale = $saleRepository->find($id);
        return new Response($utilService->serializeJSON($sale));
    }

    /**
     * @Route("/new", name="sale_new", methods={"POST"})
     */
    public function saleNew(Request $request, CustomerRepository $customerRepository, ProductRepository $productRepository)
    {
        $data = json_decode($request->getContent(), true);
        $customer = $customerRepository->find($data['customer']);
        $product = $productRepository->find($data['product']);
        $date = new DateTime();
        $sale = new Sale($customer, $product, $data['quantity'],$data['ordernumber'],$date);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($sale);
        $entityManager->flush();

        return $this->json($data);
    }

    /**
     * @Route("/edit/{id}", name="sale_edit", methods={"PUT"})
     */
    public function saleEdit($id, SaleRepository $saleRepository, CustomerRepository $customerRepository, ProductRepository $productRepository, Request $request)
    {
        $data= json_decode($request->getContent(), true);
        $customerId = $customerRepository->find($data['customer']);
        $productId = $productRepository->find($data['product']);
        
        $sale = $saleRepository->find($id);

        if (!$sale) {
            throw $this->createNotFoundException(
                'No sale found for id '.$id
            );
        }     
        
        $sale->setCustomer($customerId);
        $sale->setProduct($productId);
        $sale->setQuantity($data['quantity']);
        $sale->setOrdernumber($data['ordernumber']);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->flush();
        return $this->json("Sale updated");
    }

    /**
     * @Route("/delete/{id}", name="sale_delete", methods={"DELETE"})
     */
    public function delete(SaleRepository $saleRepository, $id)
    {
        $sale = $saleRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($sale);
        $entityManager->flush();       
        return $this->json("Sale deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }
}
