<?php


namespace App\Controller;


use App\Entity\Order;
use App\Form\NumberPlateOrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Entry point of the application
     *
     * @Route(path="/", name="home_number_plate", methods={"get", "post"})
     */
    public function enterNumberPlate(Request $request): Response
    {
        $order = $request->getSession()->get('order') ?? new Order();
        $form = $this->createForm(NumberPlateOrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->set('numberPlate', $order->getNumberPlate());
            $order->setNumberPlate(null);
            $request->getSession()->set('order', $order);
            return $this->redirectToRoute('eureka_g6k_calcul', [
                'simu' => 'cout-certificat-immatriculation',
            ]);
        }
        return $this->render('checkout/number_plate.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}