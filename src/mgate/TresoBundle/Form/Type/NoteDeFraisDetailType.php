<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\TresoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use mgate\TresoBundle\Entity\NoteDeFraisDetail;

class NoteDeFraisDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', 'textarea',
                    array('label' => 'Description de la dépense',
                        'required' => true,
                        'attr' => array(
                            'cols' => '100%',
                            'rows' => 5, ),
                        )
                    )
                ->add('prixHT', 'money', array('label' => 'Prix H.T.', 'required' => false))
                ->add('tauxTVA', 'number', array('label' => 'Taux TVA (%)', 'required' => false))
                ->add('kilometrage', 'integer', array('label' => 'Nombre de Kilomètre', 'required' => false))
                ->add('tauxKm', 'integer', array('label' => 'Prix au kilomètre (en cts)', 'required' => false))
                ->add('type', 'choice', array('choices' => NoteDeFraisDetail::getTypeChoices(), 'required' => true))
                ->add('compte', 'genemu_jqueryselect2_entity', array(
                        'class' => 'mgate\TresoBundle\Entity\Compte',
                        'property' => 'libelle',
                        'required' => false,
                        'label' => 'Catégorie',
                        'configs' => array('placeholder' => 'Sélectionnez une catégorie', 'allowClear' => true),
                        ));
    }

    public function getName()
    {
        return 'mgate_tresobundle_notedefraisdetailtype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\TresoBundle\Entity\NoteDeFraisDetail',
        ));
    }
}
