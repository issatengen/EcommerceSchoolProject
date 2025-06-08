<?php

namespace App\Form;

use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderLine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            // ->add('amount')
            // ->add('item', EntityType::class, [
            //     'class' => Item::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('orders', EntityType::class, [
            //     'class' => Order::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
        ]);
    }
}
