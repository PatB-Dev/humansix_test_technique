<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="order_index", methods={"GET"})
     */
    public function index(OrderRepository $orderRepository): Response
    {
        $total = array();
        foreach ($orderRepository->findAll() as $order){
            $sum = 0;
            foreach ($order->getProduct() as $product){
                $sum += $product->getPrice();
            }
            array_push($total, $sum);
        }

        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
            'total' => $total
        ]);
    }

    /**
     * @Route("/new", name="order_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $order->setDateCreated(new DateTime('now', new DateTimeZone('EUROPE/Paris')));

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->addUser($entityManager->getRepository(User::class)->find($this->getUser()));
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"GET"})
     */
    public function show(Order $order): Response
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="order_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="order_delete", methods={"POST"})
     */
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('order_index', [], Response::HTTP_SEE_OTHER);
    }
}
