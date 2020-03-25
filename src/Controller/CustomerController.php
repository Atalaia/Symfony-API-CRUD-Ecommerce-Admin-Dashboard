<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\UtilService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customer")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/", name="customers", methods={"GET"})
     */
    public function customerList(CustomerRepository $customerRepository, UtilService $utilService):Response
    {
        $customers = $customerRepository->findAll();
        return new Response($utilService->serializeJSON($customers));
    }

    /**
     * @Route("/{id}", name="customer_detail", methods={"GET"})
     */
    public function customerDetail(CustomerRepository $customerRepository, UtilService $utilService, $id):Response
    {
        $customer = $customerRepository->find($id);
        return new Response($utilService->serializeJSON($customer));
    }

    /**
     * @Route("/new", name="customer_new", methods={"POST"})
     */
    public function customerNew(Request $request):Response
    {
        $data = json_decode($request->getContent(), true);
        $createdAt = new DateTime();
        $customer = new Customer($data['firstname'],$data['lastname'],$data['email'],$data['password'],$data['phone'],$data['address'], $createdAt);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($customer);
        $entityManager->flush();

        return new Response('It worked. Believe me - I\'m an API');  
    }

    /**
     * @Route("/edit/{id}", name="customer_edit", methods={"PUT"})
     */
    public function customerEdit($id, CustomerRepository $customerRepository, Request $request):Response
    {
        $data= json_decode($request->getContent(), true);

        $customer = $customerRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        if (!$customer) {
            throw $this->createNotFoundException(
                'No customer found for id '.$id
            );
        }      
        $customer->setFirstname($data['firstname']);
        $customer->setLastname($data['lastname']);
        $customer->setEmail($data['email']);
        $customer->setPassword($data['password']);
        $customer->setPhone($data['phone']);
        $customer->setAddress($data['address']);
        $entityManager->flush();
        return $this->json("Customer updated");
    }

    /**
     * @Route("/delete/{id}", name="customer_delete", methods={"DELETE"})
     */
    public function delete(CustomerRepository $customerRepository, $id)
    {
        $customer = $customerRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($customer);
        $entityManager->flush();       
        return $this->json("Customer deleted");
    }
    

    public function __toString()
    {
        return $this->name;
    }

}
