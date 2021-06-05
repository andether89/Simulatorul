<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Exception\OrderCancelFailedException;
use App\Exception\SendMailFailedException;
use App\Repository\DocumentRepository;
use App\Services\NotifyService;
use App\Services\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class OrderCrudController extends AbstractCrudController
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var NotifyService
     */
    private $notifyService;
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    public function __construct(
        RequestStack $requestStack,
        NotifyService $notifyService,
        OrderService $orderService,
        DocumentRepository $documentRepository
    )
    {
        $this->requestStack = $requestStack;
        $this->notifyService = $notifyService;
        $this->orderService = $orderService;
        $this->documentRepository = $documentRepository;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('number')
                ->hideOnForm(),
            BooleanField::new('priority')
                ->renderAsSwitch(false)
                ->hideOnForm(),
            AssociationField::new('user')
                ->setTemplatePath('bundles/user_infos.html.twig')
                ->hideOnForm(),
            AssociationField::new('process')
                ->onlyOnDetail()
                ->setTemplatePath('bundles/order_process.html.twig'),
            MoneyField::new('total')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->hideOnForm(),
            ChoiceField::new('state')
                ->hideOnForm()
                ->setChoices(array_flip(Order::STATE)),
            TextField::new('paymentIntent')
                ->hideOnForm(),
            DateField::new('createdAt')
                ->hideOnForm(),
            AssociationField::new('shippingAddress')
                ->onlyOnDetail()
                ->setTemplatePath('bundles/order_address.html.twig'),
            AssociationField::new('billingAddress')
                ->onlyOnDetail()
                ->setTemplatePath('bundles/order_address.html.twig'),
            CollectionField::new('coOwners')
                ->onlyOnDetail(),
        ];
        if (Crud::PAGE_DETAIL === $pageName) {
            $documents = $this->documentRepository->findAll();
            $order = $this->getDoctrine()->getRepository(Order::class)->findOneBy(['id' => $this->requestStack->getCurrentRequest()->get('entityId')]);
            foreach ($documents as $document) {
                foreach ($document->getProcesses() as $process) {
                    if ($process->getProcessId() === $order->getProcess()->getProcessId()) {
                        $fields[] = Field::new($document->getCode() . 'File', $document->getName())
                            ->onlyOnDetail()
                            ->setTemplatePath('bundles/user_document.html.twig')
                        ;
                    }
                }
            }
        }
        if (Crud::PAGE_EDIT === $pageName) {
            $documents = $this->documentRepository->findAll();
            $order = $this->getDoctrine()->getRepository(Order::class)->findOneBy(['id' => $this->requestStack->getCurrentRequest()->get('entityId')]);
            foreach ($documents as $document) {
                foreach ($document->getProcesses() as $process) {
                    if ($process->getProcessId() === $order->getProcess()->getProcessId()) {
                        $fields[] = Field::new($document->getCode() . 'File', $document->getName())
                            ->setFormType(VichFileType::class)
                        ;
                    }
                }
            }
        }
        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
        ;

        // Si l'état de la commande le permet alors on peut la mettre à jour
        if ($this->requestStack->getCurrentRequest()->get('crudAction') === Action::DETAIL) {
            $order = $this->getDoctrine()->getRepository(Order::class)->findOneBy(['id' => $this->requestStack->getCurrentRequest()->get('entityId')]);
            if ($order->getState() < 4 && $order->getState() > 1) {
                $updateState = Action::new('updateState', 'Mettre à jour le statut à "' .  Order::STATE[$order->getState() + 1] . '"')
                    ->linkToCrudAction('updateState')
                    ->addCssClass('btn btn-primary')
                ;
                $actions
                    ->add(Crud::PAGE_DETAIL, $updateState)
                ;
            }

            if ($order->getState() < 5) {
                $cancel = Action::new('cancelOrder')
                    ->linkToCrudAction('cancelOrder')
                    ->addCssClass('btn btn-danger')
                ;
                $actions->add(Crud::PAGE_DETAIL, $cancel);
            }
            if ($order->getState() < 6 && $order->getState() > 0 && $order->getStripeSession() && $order->getPaymentIntent()) {
                $refund = Action::new('refundOrder')
                    ->linkToCrudAction('refundOrder')
                    ->addCssClass('btn btn-danger')
                ;
                $actions->add(Crud::PAGE_DETAIL, $refund);
            }
        }
        return $actions;
    }

    /**
     * Updates the state of an Order
     *
     * @param AdminContext $context
     * @return RedirectResponse
     */
    public function updateState(AdminContext $context): RedirectResponse
    {
        /** @var $order Order */
        $order = $context->getEntity()->getInstance();
        if ($order->getState() < 4) {
            $order->setState($order->getState() + 1);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'État de la commande mis à jour à "' . Order::STATE[$order->getState()] . '"');
            try {
                $this->notifyService->sendMailWithMailJet(
                    2905395,
                    'Votre commande chez Driveme.fr',
                    [
                        'orderNumber' => $order->getNumber(),
                        'orderUrl' => $this->generateUrl('account_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                        'orderState' => Order::STATE[$order->getState()],
                    ],
                    $order
                );
            } catch (SendMailFailedException $exception) {
                $this->addFlash('error', $exception->errorMessage());
            }
        }
        return $this->redirectToEntity($context);
    }


    /**
     * Cancels the Order, try a refund if the Order has already been paid
     *
     * @param AdminContext $context
     * @return RedirectResponse
     */
    public function cancelOrder(AdminContext $context): RedirectResponse
    {
        /** @var Order $order */
        $order = $context->getEntity()->getInstance();
        $difference = $this->orderService->dateDiff(time(), $order->getCreatedAt()->getTimestamp());
        try {
            $refund = $this->orderService->cancelAndRefundStripeOrder($order, false, $difference['day']);
        } catch (OrderCancelFailedException $e) {
            $this->addFlash('error', $e->errorMessage());
            return $this->redirectToEntity($context);
        }
        switch ($refund) {
            case OrderService::REFUND_SUCCEEDED:
                $this->addFlash('success', 'La commande est annulée et remboursée');
                break;
            case OrderService::REFUND_FAILED:
                $this->addFlash('error', 'La commande est éligible à un remboursement mais celui-ci n\'a pas fonctionné correctement');
                break;
            case OrderService::NO_REFUND_NEEDED:
                $this->addFlash('success', 'La commande est annulée');
                break;
            case OrderService::ALREADY_CANCELLED:
                $this->addFlash('error', 'La commande est déjà annulée');
                break;
        }
        return $this->redirectToEntity($context);
    }

    /**
     * Makes a refund even if the Order can't be refund and passed the 15days delay
     *
     * @param AdminContext $context
     * @return RedirectResponse
     */
    public function refundOrder(AdminContext $context): RedirectResponse
    {
        /** @var Order $order */
        $order = $context->getEntity()->getInstance();
        try {
            $refund = $this->orderService->makeStripeRefund($order);
        } catch (ApiErrorException $exception) {
            $this->addFlash('error', $exception->getMessage());
            return $this->redirectToEntity($context);
        }
        if ($refund->status === Refund::STATUS_SUCCEEDED) {
            $order->setState(6);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'La commande a été remboursée');
            try {
                $this->notifyService->sendMailWithMailJet(
                    2907294,
                    'Remboursement de votre commande chez Driveme.fr',
                    [
                        'orderNumber' => $order->getNumber(),
                        'orderTotal' => number_format($order->getTotal(), 2, ',', '.') . ' €',
                    ],
                    $order
                );
            } catch (SendMailFailedException $exception) {
                $this->addFlash('error', $exception->errorMessage());
            }
        } else {
            $this->addFlash('error', 'La commande n\'a pas pu être remboursée');
        }
        return $this->redirectToEntity($context);
    }

    public function redirectToEntity(AdminContext $context): RedirectResponse
    {
        $url = $this->get(AdminUrlGenerator::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($context->getEntity()->getPrimaryKeyValue())
            ->generateUrl();
        return $this->redirect($url);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityManager->persist($entityInstance);
        $entityManager->flush();
        /** @var Order $order */
        $order = $entityInstance;
        $this->orderService->setDocumentsToReUpload($order);
    }
}
