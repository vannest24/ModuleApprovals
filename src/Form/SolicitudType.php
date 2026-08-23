<?php

namespace App\Form;

use App\Entity\SolicitudesDcr;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SolicitudType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre_documento', null, ['label' => 'Nombre del Documento'])
            ->add('numero_revision', null, ['label' => 'No. Revisión'])
            ->add('link_sharepoint', null, ['label' => 'Link de SharePoint'])
            ->add('fecha_limite', null, [
                'widget' => 'single_text',
                'label' => 'Fecha Límite de Aprobación'
            ])
            ->add('aprobadores', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Seleccionar Aprobadores',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_APROBADOR%');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SolicitudesDcr::class,
            'csrf_protection' => false,
        ]);
    }
}
