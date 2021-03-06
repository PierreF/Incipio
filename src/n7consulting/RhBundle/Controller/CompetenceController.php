<?php

namespace n7consulting\RhBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use n7consulting\RhBundle\Entity\Competence;
use n7consulting\RhBundle\Form\Type\CompetenceType;

class CompetenceController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function ajouterAction()
    {
        $em = $this->getDoctrine()->getManager();

        $competence = new Competence();

        $form = $this->createForm(new CompetenceType(), $competence);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirect($this->generateUrl('n7consultingRh_competence_voir', array('id' => $competence->getId())));
            }
        }

        return $this->render('n7consultingRhBundle:Competence:ajouter.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $entities = $this->getDoctrine()->getManager()->getRepository('n7consultingRhBundle:Competence')->findAll();

        return $this->render('n7consultingRhBundle:Competence:index.html.twig', array(
            'competences' => $entities,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('n7consultingRhBundle:Competence')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Cette compétence n\'existe pas !');
        }

        $devs = $em->getRepository('mgatePersonneBundle:Membre')->findByCompetence($entity);

        $etudes = $em->getRepository('mgateSuiviBundle:Etude')->findByCompetence($entity);

        return $this->render('n7consultingRhBundle:Competence:voir.html.twig', array(
            'competence' => $entity,
            'devs' => $devs,
            'etudes' => $etudes,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$competence = $em->getRepository('n7consulting\RhBundle\Entity\Competence')->find($id)) {
            throw $this->createNotFoundException('La compétence demandée n\'existe pas !');
        }

        // On passe l'$article récupéré au formulaire
        $form = $this->createForm(new CompetenceType(), $competence);
        $deleteForm = $this->createDeleteForm($id);
        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            if ($form->isValid()) {
                $em->persist($competence);
                $em->flush();

                return $this->redirect($this->generateUrl('n7consultingRh_competence_voir', array('id' => $competence->getId())));
            }
        }

        return $this->render('n7consultingRhBundle:Competence:modifier.html.twig', array(
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Par souci de simplicité, on fait 2 requetes (une sur les competences, une sur les intervenants), alors que seule la requete sur les competences suffirait.
     */
    public function visualiserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $competences = $em->getRepository('n7consulting\RhBundle\Entity\Competence')->getCompetencesTree();
        $membres = $em->getRepository('mgatePersonneBundle:Membre')->getByCompetencesNonNul();

        return $this->render('n7consultingRhBundle:Competence:visualiser.html.twig', array(
            'competences' => $competences,
            'membres' => $membres,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$entity = $em->getRepository('n7consulting\RhBundle\Entity\Competence')->find($id)) {
                throw $this->createNotFoundException('La compétence demandée n\'existe pas !');
            }

            foreach ($entity->getMembres() as $membre) {
                $membre->setPoste(null);
            }

            //$entity->setMembres(null);

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('n7consultingRh_competence_homepage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
            ;
    }
}
