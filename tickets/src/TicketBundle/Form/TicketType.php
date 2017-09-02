<?php

namespace TicketBundle\Form;

use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType as ComponentTextType;

class TicketType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', new ComponentTextType(), array('required'=> true))
            ->add('body', new TextareaType(), array('required'=> true))
//            ->add('created', 'date')
            ->add('status', EntityType::class, array(
                'property'=>'name',
                'class'=> 'TicketBundle\Entity\Status'))
//            ->add('author')
            ->add('assignee', EntityType::class, array('required'=> true, 'empty_value'=>'--Select--',
                'property'=>'name',
                'class'=> 'TicketBundle\Entity\TicketUser'));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TicketBundle\Entity\Ticket'
        ));
    }
}
