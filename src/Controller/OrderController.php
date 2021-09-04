<?php

namespace App\Controller;

use App\Entity\Order;
use DateTimeInterface;
use App\Classes\Basket;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande", name="order")
     */
    public function index(Basket $basket, Request $request): Response
    {
        if(!$this->getUser()->getAddresses()->getValues()){
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'basket' => $basket->getAllBasket()
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="order_recap", methods={"POST"})
     */
    public function add(Basket $basket, Request $request): Response
    {
        if(!$this->getUser()->getAddresses()->getValues()){
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();
            
            $delivery_content = $delivery[0]->getFirstName() . ' ' . $delivery[0]->getLastName();
            $delivery_content .= '<br />' . $delivery[0]->getPhone();

            if($delivery[0]->getCompany()){
                $delivery_content .= '<br/>' . $delivery[0]->getCompany();
            }

            $delivery_content .= '<br/>' . $delivery[0]->getAddress();
            $delivery_content .= '<br/>' . $delivery[0]->getZip() . ' ' . $delivery[0]->getCity();
            $delivery_content .= '<br/>' . $delivery[0]->getCountry();

            $order = new Order();
            
            $order->setUser($this->getUser());
            $order->setCarrierName($carriers[0]->getName());
            $order->setCarrierPrice($carriers[0]->getPrice());
            $order->setDelivery($delivery_content);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);


            foreach ($basket->getAllBasket() as $product) {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['jeuxvideo']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['jeuxvideo']->getPrice());
                $orderDetails->setTotal($product['jeuxvideo']->getPrice() * $product['quantity']);

                $this->entityManager->persist($orderDetails);
            }
            
            $this->entityManager->flush();

            return $this->render('order/add.html.twig', [
                'basket' => $basket->getAllBasket(),
                'carrier' => $carriers,
                'delivery' => $delivery_content
            ]);
        }

        return $this->redirectToRoute('basket');
        
    }
}