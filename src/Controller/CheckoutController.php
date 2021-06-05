<?php


namespace App\Controller;


use App\Entity\Order;
use App\Entity\OrderProcess;
use App\Exception\OrderNotCompleteException;
use App\Form\OrderType;
use App\Services\OrderService;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(path="/checkout")
 */
class CheckoutController extends AbstractController
{
    /**
     * @var OrderService
     */
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Entry point for the beginning of an Order, the User has to give informations about Address he want to use
     * for the Order
     *
     * @Route(path="/order-infos", name="checkout_order_infos", methods={"post", "get"})
     */
    public function orderInfos(Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->has('demarche')) {
            return $this->redirectToRoute('home_number_plate');
        }

        /** @var OrderProcess $orderProcess */
        $orderProcess = $session->get('demarche');
        if (empty($orderProcess->getProcessType())) {
            return $this->redirectToRoute('home_number_plate');
        }
        $order = $session->get('order') ?? new Order();
        $order
            ->setProcess($orderProcess)
            ->setTotal((float)$orderProcess->getTotalToPay())
        ;

        if ($session->has('shippingAddress')) {
            $order->setShippingAddress($session->get('shippingAddress'));
        }
        if ($session->has('billingAddress')) {
            $order->setBillingAddress($session->get('billingAddress'));
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('shippingAddress', $order->getShippingAddress());
            if ($order->getBillingAddress()) {
                $session->set('billingAddress', $order->getBillingAddress());
            }
            $order->setShippingAddress(null);
            $order->setBillingAddress(null);

            $session->set('order', $order);

            if (!$this->isGranted('ROLE_USER')) {
                $request->getSession()->set('fromCheckout', true);
                return $this->redirectToRoute('app_create_account');
            }

            return $this->redirectToRoute('checkout_summary');
        }
        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Entry point for the summary of the order, right before the payment
     *
     * @Route(path="/summary", name="checkout_summary", methods={"get"})
     */
    public function summary()
    {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('checkout_order_infos');
        }

        try {
            $order = $this->orderService->getCompleteOrderFromSession();
        } catch (OrderNotCompleteException $exception) {
            return $this->redirectToRoute('checkout_order_infos');
        }

        return $this->render('checkout/summary.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * Entry point for the Stripe Payment, also saves the Order. Only if User agreed with condition terms
     *
     * @Route(path="/payment", name="checkout_pay", methods={"post"})
     */
    public function payOrder(Request $request): JsonResponse
    {
        $form = json_decode($request->getContent());
        if (!$this->isGranted('ROLE_USER') || !$this->isCsrfTokenValid('pay-order', $form[0])) {
            throw $this->createAccessDeniedException();
        }

        if ($form[2] === false) {
            return $this->json('Erreur : Vous devez accepter les conditions générales', 500);
        }

        try {
            $order = $this->orderService->validateOrder();
        } catch (OrderNotCompleteException $exception) {
            return $this->json('Erreur : Votre commande est inexistante', 500);
        }
        try {
            return $this->orderService->makeStripePayment(
                $order,
                $this->generateUrl('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        } catch (ApiErrorException $exception) {
            return $this->json('Erreur : une erreur est survenue avec le service de paiement, merci de réessayer après quelques instants');
        }
    }
}