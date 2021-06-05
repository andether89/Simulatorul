<?php

namespace App\Form;

use App\Entity\Order;
use App\Repository\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserDocumentType extends AbstractType
{
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $documents = $this->documentRepository->findAll();
        $optionsArray = [
            'required' => false,
            'download_link' => false,
            'allow_delete' => false,
            'label' => '',
        ];
        $alternativeOptionsArray = [
            'disabled' => true,
            'label' => '',
        ];
        /** @var Order $order */
        $order = $options['data'];

        foreach ($documents as $document) {
            foreach ($document->getProcesses() as $process) {
                if ($order->getProcess()->getProcessId() === $process->getProcessId()) {
                    $parts = preg_split('/(?=[A-Z])/', $document->getCode(), -1, PREG_SPLIT_NO_EMPTY);
                    $label = ucfirst($parts[0]) . ' ' . strtolower($parts[1]);
                    $function = 'get' . ucfirst($parts[0]) . $parts[1] . 'Name';
                    $optionsArray['label'] = $label;
                    $alternativeOptionsArray['label'] = $label;
                    if (empty(call_user_func([$order, $function]))) {
                        $builder->add($document->getCode() . 'File', VichFileType::class, $optionsArray);
                    } else {
                        $builder->add($document->getCode() . 'OriginalName', TextType::class, $alternativeOptionsArray);
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'translation_domain' => 'forms',
        ]);
    }
}
