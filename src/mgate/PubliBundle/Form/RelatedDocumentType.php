<?php

namespace mgate\PubliBundle\Form;

use mgate\PubliBundle\Entity\RelatedDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilder;
use mgate\PersonneBundle\Form\PersonneType;
use mgate\PersonneBundle\Entity\PersonneRepository as PersonneRepository;

class RelatedDocumentType extends AbstractType {

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options){
        if($options['etude'])
        $builder->add('etude', 'genemu_jqueryselect2_entity', array(
                    'class' => 'mgate\SuiviBundle\Entity\Etude',
                    'property' => 'reference',
                    'required' => false,
                    'label' => 'Document lié à l\'étude',
                    'configs' => array('placeholder' => 'Sélectionnez une étude', 'allowClear' => true)
                ));
        if($options['prospect'])
        $builder->add('prospect', 'genemu_jqueryselect2_entity', array(
                    'class' => 'mgate\PersonneBundle\Entity\Prospect',
                    'property' => 'nom',
                    'required' => false,
                    'label' => 'Document lié au prospect',
                    'configs' => array('placeholder' => 'Sélectionnez un prospect', 'allowClear' => true)
                ));
        if($options['formation'])
        $builder->add('formation', 'genemu_jqueryselect2_entity', array(
                    'class' => 'mgate\FormationBundle\Entity\Formation',
                    'property' => 'titre',
                    'required' => false,
                    'label' => 'Document lié à la formation',
                    'configs' => array('placeholder' => 'Sélectionnez une formation', 'allowClear' => true)
                ));
        if($options['etudiant'] || $options['etude'])
        $builder->add('membre', 'genemu_jqueryselect2_entity', array(
                    'label' => 'Document lié à l\'étudiant',
                    'class' => 'mgate\\PersonneBundle\\Entity\\Membre',
                    'property' => 'personne.prenomNom',
                    'required' => false,
                    'configs' => array('placeholder' => 'Sélectionnez un étudiant', 'allowClear' => true)))
        ;
    }
    
    public function getName() {
        return 'mgate_suivibundle_categoriedocumenttype';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'mgate\PubliBundle\Entity\RelatedDocument',
            'etude'     => null,
            'etudiant'  => null,
            'prospect'  => null,
            'formation' => null,
        ));
    }
}