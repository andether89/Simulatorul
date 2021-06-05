<?php

namespace App\Controller\Admin;

use App\Entity\Process;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProcessCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Process::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            IntegerField::new('process_id'),
        ];
    }
}
