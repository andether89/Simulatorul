<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Entity\Order;
use App\Entity\Process;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractDashboardController
{
    /**
     * @Route("/app-private-space/orders", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();
        return $this->redirect($routeBuilder->setController(OrderCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Simulateur')
            ->setTranslationDomain('admin')
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Orders', 'fas fa-shipping-fast', Order::class);
        yield MenuItem::linkToCrud('Processes', 'fas fa-step-forward', Process::class);
        yield MenuItem::linkToCrud('Documents', 'fas fa-file-upload', Document::class);
    }
}
