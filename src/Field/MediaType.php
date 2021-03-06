<?php

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */

namespace Darkanakin41\MediaBundle\Field;

use Darkanakin41\MediaBundle\DependencyInjection\Darkanakin41MediaExtension;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    /**
     * @var array
     */
    private $bundleConfiguration;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->bundleConfiguration = $parameterBag->get(Darkanakin41MediaExtension::CONFIG_KEY);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'category' => 'all',
            'class' => $this->bundleConfiguration['file_class'],
            'choice_label' => 'filename',
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['category'] = $options['category'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $category = $options['category'];
        $builder->setAttribute('category', $options['category']);
        $builder->setAttribute('query_builder', function (EntityRepository $fr) use ($category) {
            $qb = $fr->createQueryBuilder('f');
            $qb->where('f.category = :category');
            $qb->orderBy('f.filename');
            $qb->setParameter('category', $category);

            return $qb;
        });
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
