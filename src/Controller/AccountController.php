<?php


namespace App\Controller;


use App\Entity\Order;
use App\Exception\CheckPaymentFailedException;
use App\Exception\OrderCancelFailedException;
use App\Form\UserDocumentType;
use App\Services\OrderService;
use Exception;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(path="/profile")
 */
class AccountController extends AbstractController
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
     * Entry point of the index of the user profile
     *
     * @Route(path="/", name="account_index")
     */
    public function index(): Response
    {
        return $this->render('user/account/index.html.twig');
    }

    /**
     * Entry point for the list of orders the user has done
     *
     * @Route(path="/order", name="account_order")
     */
    public function order(): Response
    {
        return $this->render('user/account/order.html.twig');
    }

    /**
     * Entry point to see more information about one order for the user
     *
     * @Route(path="/order-{id}", name="account_order_show", methods={"get", "post"})
     */
    public function showOrder(Order $order, Request $request): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $difference = $this->orderService->dateDiff(time(), $order->getCreatedAt()->getTimestamp());

        try {
            $payment = $this->orderService->validateStripePayment($order, $this->generateUrl('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
        } catch (CheckPaymentFailedException $exception) {
            $this->addFlash('error', $exception->errorMessage());
            return $this->render('user/account/order_show.html.twig', [
                'order' => $order,
                'difference' => $difference,
            ]);
        }

        switch ($payment) {
            case OrderService::PAYMENT_SUCCESS:
                $this->addFlash('success', '');
                break;
            case OrderService::PAYMENT_FAILED:
                $this->addFlash('error', '');
                break;
            case OrderService::NO_PAYMENT_ATTEMPT_OR_ALREADY_PAID:
                break;
        }
        $variables = [];

        if ($order->getState() === 1) {
            $form = $this->createForm(UserDocumentType::class, $order);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->orderService->verifyUserDocuments($order);
                return $this->redirectToRoute('account_order_show', [
                    'id' => $order->getId(),
                ]);
            }
            $variables['form'] = $form->createView();
        }
        $variables['order'] = $order;
        $variables['difference'] = $difference;

        return $this->render('user/account/order_show.html.twig', $variables);
    }

    /**
     * Entry point for the user to cancel an order if 15 days didn't pass since he ordered
     *
     * @Route(path="/cancel-order-{id}", name="account_cancel_order", methods={"post"})
     */
    public function cancelOrder(Order $order, Request $request): RedirectResponse
    {
        if ($order->getUser() !== $this->getUser() || !$this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        if ($order->getState() >= 5) {
            throw new NotFoundHttpException();
        }

        $difference = $this->orderService->dateDiff(time(), $order->getCreatedAt()->getTimestamp());
        if ($difference['day'] > 15) {
            $this->addFlash('error', 'Vous avez dépassé le délai de 15 jours passé lequel vous ne pouvez plus vous faire rembourser');
            return $this->redirectToRoute('account_order_show', [
                'id' => $order->getId(),
            ]);
        }

        try {
            $refund = $this->orderService->cancelAndRefundStripeOrder($order, true, $difference['day']);
        } catch (OrderCancelFailedException $e) {
            $this->addFlash('error', $e->errorMessage());
            return $this->redirectToRoute('account_order_show', [
                'id' => $order->getId(),
            ]);
        }

        switch ($refund) {
            case OrderService::REFUND_SUCCEEDED:
                $this->addFlash('success', 'La commande a bien été annulée et sera remboursée dans les 5 à 10 jours à compter de l\'annulation');
                break;
            case OrderService::REFUND_FAILED:
                $this->addFlash('error', 'Une erreur est survenue lors de la demande de remboursement');
                break;
            case OrderService::NO_REFUND_NEEDED:
                $this->addFlash('success', 'Votre commande a bien été annulée');
                break;
            case OrderService::ALREADY_CANCELLED:
                $this->addFlash('error', 'Votre commande est déjà annulée');
                break;
        }

        return $this->redirectToRoute('account_order_show', [
            'id' => $order->getId(),
        ]);
    }

    /**
     * Entry point for Stripe to pay an Order
     *
     * @Route(path="/payment-{id}", name="account_pay", methods={"post"})
     * @throws Exception
     */
    public function payOrder(Order $order, Request $request): JsonResponse
    {
        if ($order->getState() > 0 || !$this->isCsrfTokenValid('pay-order' . $order->getId(), $request->getContent())) {
            throw new NotFoundHttpException();
        }
        if ($this->getUser() !== $order->getUser()) {
            throw new AccessDeniedHttpException();
        }
        try {
            return $this->orderService->makeStripePayment(
                $order,
                $this->generateUrl('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        } catch (ApiErrorException $exception) {
            return $this->json('Erreur : votre paiement n\'a pas fonctionné, merci de réessayer après quelques instants');
        }
    }
}