<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId)
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        //vider la session 'cart'
        $cart->remove();
        if (!$order->getState() == 0) {

            //Modifier le statut isPaid de ma commande en mettant 1 dans la BDD
            $order->setState(1);
            $this->entityManager->flush();
        }


        //envoyer un mail à notre client pour lui confirmer sa commande. A réparer !
        $mail = new Mail();
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>Merci pour votre commande !<br/><br/> Site de vêtments, de sneakers et de goodies.";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande sur La boutique de Paki est validée !', $content);

        //afficher les infos de la commande de l'utilisateur

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
