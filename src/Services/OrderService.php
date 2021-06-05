<?php


namespace App\Services;


use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderNumber;
use App\Entity\User;
use App\Exception\CheckPaymentFailedException;
use App\Exception\OrderCancelFailedException;
use App\Entity\StripeOrderInterface;
use App\Exception\OrderNotCompleteException;
use App\Exception\SendMailFailedException;
use App\Repository\DocumentRepository;
use App\Repository\OrderNumberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class OrderService
{
    public const REFUND_SUCCEEDED = 'refund_succeeded';
    public const REFUND_FAILED = 'refund_failed';
    public const NO_REFUND_NEEDED = 'no_refund_needed';
    public const ALREADY_CANCELLED = 'already_cancelled';

    public const PAYMENT_SUCCESS = 'payment_success';
    public const PAYMENT_FAILED = 'payment_failed';
    public const NO_PAYMENT_ATTEMPT_OR_ALREADY_PAID = 'no_payment_attempt_or_already_paid';

    /**
     * @var string
     */
    private $stripeKey;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var NotifyService
     */
    private $notifyService;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    public function __construct(
        string $stripeKey,
        EntityManagerInterface $entityManager,
        NotifyService $notifyService,
        RequestStack $requestStack,
        Security $security,
        UrlGeneratorInterface $urlGenerator,
        DocumentRepository $documentRepository
    )
    {
        $this->stripeKey = $stripeKey;
        $this->entityManager = $entityManager;
        $this->notifyService = $notifyService;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->documentRepository = $documentRepository;
    }

    /**
     * Cancels the Order and make a refund with Stripe if a payment has been registered for the Order
     * and then sends an email to the User to confirm
     *
     * @param StripeOrderInterface $order The Order to cancel and potentially refund
     * @param bool $byUser If the Order is cancelled by User
     * @param int $differenceDay The number of days since the Order has been done, if bigger than 15 no refund is done
     * @return string A constant REFUND_SUCCEEDED if success, REFUND_FAILED if failed, NO_REFUND_NEEDED
     * if customer didn't paid
     * @throws OrderCancelFailedException
     */
    public function cancelAndRefundStripeOrder(StripeOrderInterface $order, bool $byUser, int $differenceDay): string
    {
        if ($order->getState() >= 5) {
            return self::ALREADY_CANCELLED;
        }
        if ($order->getPaymentIntent() && $order->getStripeSession() && $order->getState() > 0 && $differenceDay <= 15) {
            try {
                $refund = $this->makeStripeRefund($order);
            } catch (ApiErrorException $e) {
                throw new OrderCancelFailedException($e->getMessage());
            }
            if ($refund->status === Refund::STATUS_SUCCEEDED) {
                $order->setState(6);
                $return = self::REFUND_SUCCEEDED;
            } else {
                $order->setState(5);
                $return = self::REFUND_FAILED;
            }
        } else {
            $order->setState(5);
            $return = self::NO_REFUND_NEEDED;
        }
        try {
            if ($byUser) {
                $this->notifyService->sendMailWithMailJet(
                    2905192,
                    'Annulation de votre commande chez Driveme.fr',
                    [
                        'orderNumber' => $order->getNumber(),
                        'orderTotal' => number_format($order->getTotal(), 2, ',', '.') . ' €',
                    ],
                    $order
                );
            } else {
                $this->notifyService->sendMailWithMailJet(
                    2906579,
                    'Annulation de votre commande chez Driveme.fr',
                    [
                        'orderNumber' => $order->getNumber(),
                        'orderTotal' => number_format($order->getTotal(), 2, ',', '.') . ' €',
                    ],
                    $order
                );
            }
        } catch (SendMailFailedException $exception) {
            // Nothing to do with this error
        }
        $this->entityManager->flush();
        return $return;
    }

    /**
     * Verify if the Order has been paid or not, if yes, updates the Order State and sends an email to
     * confirm the payment to the User
     *
     * @param StripeOrderInterface $order The Order to check payment
     * @param string $orderUrl The Order Url for the Email
     * @return string PAYMENT_SUCCESS if success, PAYMENT_FAILED if failed, NO_PAYMENT_ATTEMPT_OR_ALREADY_PAID if
     * no payment attempt are registered or if the Order has already been paid
     * @throws CheckPaymentFailedException
     */
    public function validateStripePayment(StripeOrderInterface $order, string $orderUrl): string
    {
        if ($order->getStripeSession() && $order->getState() === 0) {
            try {
                Stripe::setApiKey($this->stripeKey);
                $session = Session::retrieve($order->getStripeSession());
            } catch (ApiErrorException $e) {
                throw new CheckPaymentFailedException($e->getMessage());
            }
            if ($session->payment_status === Session::PAYMENT_STATUS_PAID) {
                $order->setPaymentIntent($session->payment_intent)
                    ->setState(1)
                ;
                $this->entityManager->flush();
                try {
                    $this->notifyService->sendMailWithMailJet(
                        2905183,
                        'Paiement de votre commande chez Driveme.fr',
                        [
                            'orderNumber' => $order->getNumber(),
                            'orderTotal' => number_format($order->getTotal(), 2, ',', '.') . ' €',
                            'orderUrl' => $orderUrl
                        ],
                        $order
                    );
                } catch (SendMailFailedException $exception) {
                    // Nothing to do with this error
                }
                return self::PAYMENT_SUCCESS;
            } else {
                return self::PAYMENT_FAILED;
            }
        }
        return self::NO_PAYMENT_ATTEMPT_OR_ALREADY_PAID;
    }

    /**
     * Creates a refund for the Order
     *
     * @param StripeOrderInterface $order The Order to refund
     * @return Refund
     * @throws ApiErrorException
     */
    public function makeStripeRefund(StripeOrderInterface $order): Refund
    {
        Stripe::setApiKey($this->stripeKey);
        return Refund::create([
            'payment_intent' => $order->getPaymentIntent(),
            'reason' => Refund::REASON_REQUESTED_BY_CUSTOMER
        ]);
    }

    /**
     * Creates a payment session with Stripe
     *
     * @param StripeOrderInterface $order The Order you want to pay for
     * @param string $successUrl The Url in case of success
     * @param string $cancelUrl The Url in case of cancel
     * @return JsonResponse
     * @throws ApiErrorException
     */
    public function makeStripePayment(StripeOrderInterface $order, string $successUrl, string $cancelUrl): JsonResponse
    {
        Stripe::setApiKey($this->stripeKey);
        $session = Session::create([
            'payment_method_types' => ['card'],
            'client_reference_id' => $order->getUser()->getId(),
            'customer_email' => $order->getUser()->getEmail(),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Commande n°' . $order->getNumber(),
                    ],
                    'unit_amount' => number_format($order->getTotal(), 2, '', ''),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl
        ]);
        $order->setStripeSession($session->id);
        $this->entityManager->flush();
        return new JsonResponse(['id' => $session->id]);
    }

    /**
     * Get the complete Order from the session
     *
     * @throws OrderNotCompleteException
     * @return Order
     */
    public function getCompleteOrderFromSession(): Order
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();

        if (!$session->has('order') || !$session->has('shippingAddress')) {
            throw new OrderNotCompleteException();
        }

        /** @var $order Order */
        $order = $session->get('order');

        /** @var Address $shippingAddress */
        $shippingAddress = $session->get('shippingAddress');
        $billingAddress = null;
        if ($shippingAddress->getId()) {
            $shippingAddress->setId(null);
        }
        if ($session->has('billingAddress')) {
            /** @var Address $billingAddress */
            $billingAddress = $session->get('billingAddress');
            if ($billingAddress->getId()) {
                $billingAddress->setId(null);
            }
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $order
            ->setUser($user)
            ->setState(0)
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
        ;
        if ($session->has('numberPlate')) {
            $order->setNumberPlate($session->get('numberPlate'));
        }
        return $order;
    }

    /**
     * Validate and saves the Order in the Database
     *
     * @return Order
     * @throws OrderNotCompleteException
     */
    public function validateOrder(): Order
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
        $order = $this->getCompleteOrderFromSession();
        $form = json_decode($this->requestStack->getCurrentRequest()->getContent());

        if ($form[1]) {
            $order->setTotal($order->getTotal() + 9.99);
        }
        if ($form[3]) {
            $order->setTotal($order->getTotal() + 1);
        }
        $order->setPriority($form[1]);
        $order->setSendSms($form[3]);
        $order->setNumber(
            'DRIVEME' . date('Ymd') . (100000 + $this->generateOrderNumber($this->entityManager->getRepository(OrderNumber::class)))
        );
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $session = $this->requestStack->getCurrentRequest()->getSession();
        $session->remove('demarche');
        $session->remove('order');
        $session->remove('numberPlate');

        if ($session->has('billingAddress')) {
            /** @var Address $billingAddress */
            $billingAddress = $session->get('billingAddress');
            $billingAddress->setId(null);
        }
        /** @var Address $shippingAddress */
        $shippingAddress = $session->get('shippingAddress');
        $shippingAddress->setId(null);

        try {
            $this->notifyService->sendMailWithMailJet(
                2903636,
                'Votre commande chez Driveme.fr',
                [
                    'orderNumber' => $order->getNumber(),
                    'orderTotal' => number_format($order->getTotal(), 2, ',', '.') . ' €',
                    'orderUrl' => $this->urlGenerator->generate('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                $order
            );
        } catch (SendMailFailedException $exception) {
            // Nothing to do with this error
        }
        return $order;
    }

    /**
     * Verify which documents have been sent by the User, if all of them have been sent,
     * updates the State of the Order
     *
     * @param Order $order The current Order
     */
    public function verifyUserDocuments(Order $order)
    {
        $this->entityManager->flush();
        $empty = false;
        $documents = $this->documentRepository->findAll();
        foreach ($documents as $document) {
            foreach ($document->getProcesses() as $process) {
                if ($order->getProcess()->getProcessId() === $process->getProcessId()) {
                    $parts = preg_split('/(?=[A-Z])/', $document->getCode(), -1, PREG_SPLIT_NO_EMPTY);
                    $function = 'get' . ucfirst($parts[0]) . $parts[1] . 'Name';
                    if (empty(call_user_func([$order, $function]))) {
                        $empty = true;
                    }
                }
            }
        }
        if (!$empty && $order->getState() === 1) {
            $order->setState(2);
            $this->entityManager->flush();
            // TODO : send a confirmation email
        }
    }

    public function setDocumentsToReUpload(Order $order)
    {
        $documentsToReUpload = [];
        $documents = $this->documentRepository->findAll();
        foreach ($documents as $document) {
            foreach ($document->getProcesses() as $process) {
                if ($order->getProcess()->getProcessId() === $process->getProcessId()) {
                    $parts = preg_split('/(?=[A-Z])/', $document->getCode(), -1, PREG_SPLIT_NO_EMPTY);
                    $function = 'get' . ucfirst($parts[0]) . $parts[1] . 'Name';
                    if (empty(call_user_func([$order, $function]))) {
                        $documentsToReUpload[] = $document->getName();
                    }
                }
            }
        }
        if (!empty($documentsToReUpload)) {
            $documentsToReUploadString = join(', ', $documentsToReUpload);
            try {
                $this->notifyService->sendMailWithMailJet(
                    2938982,
                    'Un soucis avec les documents que vous nous avez envoyé',
                    [
                        'orderNumber' => $order->getNumber(),
                        'documents' => $documentsToReUploadString,
                        'orderUrl' => $this->urlGenerator->generate('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    $order
                );
            } catch (SendMailFailedException $exception) {
                // Nothing to do with this exception
            }
            $order->setState(1);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }
    }

    /**
     * Calculate the difference of days/hours/minutes between current and previous date
     *
     * @param string $currentDate
     * @param string $previousDate
     * @return array
     */
    public function dateDiff(string $currentDate, string $previousDate): array
    {
        $diff = abs($currentDate - $previousDate);
        $retour = [];

        $tmp = $diff;
        $retour['second'] = $tmp % 60;

        $tmp = floor(($tmp - $retour['second']) / 60);
        $retour['minute'] = $tmp % 60;

        $tmp = floor( ($tmp - $retour['minute'])/60 );
        $retour['hour'] = $tmp % 24;

        $tmp = floor( ($tmp - $retour['hour'])  /24 );
        $retour['day'] = $tmp;

        return $retour;
    }

    /**
     * Generates an Order Number
     */
    private function generateOrderNumber(OrderNumberRepository $numberRepository): int
    {
        $lastNumbers = $numberRepository->findLast();
        $number = new OrderNumber();
        if (empty($lastNumbers)) {
            $orderNumber = 1;
            $number->setNumber($orderNumber);
        } else {
            foreach ($lastNumbers as $lastNumber) {
                $number->setNumber($lastNumber->getNumber() + 1);
            }
        }
        $this->entityManager->persist($number);
        $this->entityManager->flush();
        return $number->getNumber();
    }
}