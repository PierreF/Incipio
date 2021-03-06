<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Controller;

use mgate\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Form\Type\EtudeType;
use mgate\SuiviBundle\Form\Type\SuiviEtudeType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

define('STATE_ID_EN_NEGOCIATION', 1);
define('STATE_ID_EN_COURS', 2);
define('STATE_ID_EN_PAUSE', 3);
define('STATE_ID_TERMINEE', 4);
define('STATE_ID_AVORTEE', 5);

class EtudeController extends Controller
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function indexAction($page)
    {
        $MANDAT_MAX = $this->get('mgate.etude_manager')->getMaxMandat();
        $MANDAT_MIN = $this->get('mgate.etude_manager')->getMinMandat();

        $em = $this->getDoctrine()->getManager();

        //Etudes En Négociation : stateID = 1
        $etudesEnNegociation = $em->getRepository('mgateSuiviBundle:Etude')->getPipeline(array('stateID' => STATE_ID_EN_NEGOCIATION), array('mandat' => 'DESC', 'num' => 'DESC'));

        //Etudes En Cours : stateID = 2
        $etudesEnCours = $em->getRepository('mgateSuiviBundle:Etude')->getPipeline(array('stateID' => STATE_ID_EN_COURS), array('mandat' => 'DESC', 'num' => 'DESC'));

        //Etudes en pause : stateID = 3
        $etudesEnPause = $em->getRepository('mgateSuiviBundle:Etude')->getPipeline(array('stateID' => STATE_ID_EN_PAUSE), array('mandat' => 'DESC', 'num' => 'DESC'));

        //Etudes Terminees et Avortees Chargée en Ajax dans getEtudesAsyncAction
        //On push des arrays vides pour avoir les menus déroulants
        $etudesTermineesParMandat = array();
        $etudesAvorteesParMandat = array();

        $junior = $this->container->getParameter('junior');
        for ($i = $MANDAT_MIN; $i <= $MANDAT_MAX; ++$i) {
            array_push($etudesTermineesParMandat, array());
            array_push($etudesAvorteesParMandat, array());
        }

        return $this->render('mgateSuiviBundle:Etude:index.html.twig', array(
            'etudesEnNegociation' => $etudesEnNegociation,
            'etudesEnCours' => $etudesEnCours,
            'etudesEnPause' => $etudesEnPause,
            'etudesTermineesParMandat' => $etudesTermineesParMandat,
            'etudesAvorteesParMandat' => $etudesAvorteesParMandat,
            'anneeCreation' => $junior['anneeCreation'],
            'mandatMax' => $MANDAT_MAX,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function getEtudesAsyncAction()
    {
        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');

        if ($request->getMethod() == 'GET') {
            $mandat = $request->query->get('mandat');
            $stateID = $request->query->get('stateID');

            // CHECK VAR ATTENTION INJECTION SQL ?
            $etudes = $em->getRepository('mgateSuiviBundle:Etude')->findBy(array('stateID' => $stateID, 'mandat' => $mandat), array('num' => 'DESC'));

            if ($stateID == STATE_ID_TERMINEE) {
                return $this->render('mgateSuiviBundle:Etude:Tab/EtudesTerminees.html.twig', array('etudes' => $etudes));
            } elseif ($stateID == STATE_ID_AVORTEE) {
                return $this->render('mgateSuiviBundle:Etude:Tab/EtudesAvortees.html.twig', array('etudes' => $etudes));
            }
        } else {
            return $this->render('mgateSuiviBundle:Etude:Tab/EtudesAvortees.html.twig', array(
                'etudes' => null,
            ));
        }
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function stateAction(Request $request)
    {
        if ($this->get('request')->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            $stateDescription = !empty($request->request->get('state')) ? $request->request->get('state') : '';
            $stateID = !empty($request->request->get('id')) ? intval($request->request->get('id')) : 0;
            $etudeID = !empty($request->request->get('etude')) ? intval($request->request->get('etude')) : 0;

            if (!$etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($etudeID)) {
                throw $this->createNotFoundException('L\'étude n\'existe pas !');
            } else {
                $etude->setStateDescription($stateDescription);
                $etude->setStateID($stateID);
                $em->persist($etude);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('mgateSuivi_state'));
        }

        return new Response('ok !');
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function addAction()
    {
        $etude = new Etude();

        $etude->setMandat($this->get('mgate.etude_manager')->getMaxMandat());
        $etude->setNum($this->get('mgate.etude_manager')->getNouveauNumero());

        $user = $this->getUser();
        if (is_object($user) && $user instanceof User) {
            $etude->setSuiveur($user->getPersonne());
        }

        $form = $this->createForm(new EtudeType(), $etude);
        $em = $this->getDoctrine()->getManager();

        $error_messages = array();
        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            if ($form->isValid()) {
                if (!$etude->isKnownProspect()) {
                    $etude->setProspect($etude->getNewProspect());
                }

                $em->persist($etude);
                $em->flush();

                if ($this->get('request')->get('ap')) {
                    return $this->redirect($this->generateUrl('mgateSuivi_ap_rediger', array('id' => $etude->getId())));
                } else {
                    return $this->redirect($this->generateUrl('mgateSuivi_etude_voir', array('nom' => $etude->getNom())));
                }
            } else {
                //constitution du tableau d'erreurs
                $errors = $this->get('validator')->validate($etude);
                foreach ($errors as $error) {
                    array_push($error_messages, $error->getPropertyPath().' : '.$error->getMessage());
                }
            }
        }

        return $this->render('mgateSuiviBundle:Etude:ajouter.html.twig', array(
            'form' => $form->createView(),
            'errors' => $error_messages,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function voirAction(Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        //get contacts clients
        $clientContacts = $em->getRepository('mgateSuiviBundle:ClientContact')->getByEtude($etude,array('date' => 'desc'));

        $chartManager = $this->get('mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'suivi');

        $formSuivi = $this->createForm(new SuiviEtudeType(), $etude);

        return $this->render('mgateSuiviBundle:Etude:voir.html.twig', array(
            'etude' => $etude,
            'formSuivi' => $formSuivi->createView(),
            'chart' => $ob,
            'clientContacts' => $clientContacts,
            /* 'delete_form' => $deleteForm->createView(),  */));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function modifierAction(Etude $etude)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $form = $this->createForm(new EtudeType(), $etude);

        $deleteForm = $this->createDeleteForm($etude);
        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            if ($form->isValid()) {
                $em->persist($etude);
                $em->flush();

                return $this->redirect($this->generateUrl('mgateSuivi_etude_voir', array('nom' => $etude->getNom())));
            }
        }

        return $this->render('mgateSuiviBundle:Etude:modifier.html.twig', array(
            'form' => $form->createView(),
            'etude' => $etude,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Etude $etude
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Etude $etude, Request $request)
    {
        $form = $this->createDeleteForm($etude);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($etude);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Etude supprimée');

        }

        return $this->redirect($this->generateUrl('mgateSuivi_etude_homepage'));
    }

    private function createDeleteForm(Etude $etude)
    {
        return $this->createFormBuilder(array('id' => $etude->getId()))
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function suiviAction()
    {
        $em = $this->getDoctrine()->getManager();

        $MANDAT_MAX = 10;

        $etudesParMandat = array();

        for ($i = 1; $i < $MANDAT_MAX; ++$i) {
            array_push($etudesParMandat, $em->getRepository('mgateSuiviBundle:Etude')->findBy(array('mandat' => $i), array('num' => 'DESC')));
        }

        //WARN
        /* Création d'un form personalisé sans classes (Symfony Forms without Classes)
         *
         * Le problème qui se pose est de savoir si les données reçues sont bien destinées aux bonnes études
         * Si quelqu'un ajoute une étude ou supprime une étude pendant la soumission de se formulaire, c'est la cata
         * tout se décale de 1 étude !!
         * J'ai corrigé ce bug en cas d'ajout d'une étude. Les changements sont bien sauvegardés !!
         * Mais cette page doit être rechargée et elle l'est automatiquement. (Si js est activé !)
         * bref rien de bien fracassant. Solution qui se doit d'être temporaire bien que fonctionnelle !
         * Cependant en cas de suppression d'une étude, chose qui n'arrive pas tous les jours, les données seront perdues !!
         * Perdues Perdues !!!
         */
        $etudesEnCours = array();

        $NbrEtudes = 0;
        foreach ($etudesParMandat as $etudesInMandat) {
            $NbrEtudes += count($etudesInMandat);
        }

        $form = $this->createFormBuilder();

        $id = 0;
        foreach (array_reverse($etudesParMandat) as $etudesInMandat) {
            foreach ($etudesInMandat as $etude) {
                $form = $form->add((string) (2 * $id), 'hidden', array('label' => 'refEtude', 'data' => $etude->getReference()))
                    ->add((string) (2 * $id + 1), 'textarea', array('label' => $etude->getReference(), 'required' => false, 'data' => $etude->getStateDescription()));
                ++$id;
                if ($etude->getStateID() == STATE_ID_EN_COURS) {
                    array_push($etudesEnCours, $etude);
                }
            }
        }
        $form = $form->getForm();

        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));

            $data = $form->getData();

            $id = 0;
            foreach (array_reverse($etudesParMandat) as $etudesInMandat) {
                foreach ($etudesInMandat as $etude) {
                    if ($data[2 * $id] == $etude->getReference()) {
                        if ($data[2 * $id] != $etude->getStateDescription()) {
                            $etude->setStateDescription($data[2 * $id + 1]);
                            $em->persist($etude);
                            ++$id;
                        }
                    } else {
                        echo '<script>location.reload();</script>';
                    }
                }
            }
            $em->flush();
        }

        $chartManager = $this->get('mgate.chart_manager');
        $ob = $chartManager->getGanttSuivi($etudesEnCours);

        return $this->render('mgateSuiviBundle:Etude:suiviEtudes.html.twig', array(
            'etudesParMandat' => $etudesParMandat,
            'form' => $form->createView(),
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function suiviQualiteAction()
    {
        $em = $this->getDoctrine()->getManager();

        $etudesEnCours = $em->getRepository('mgateSuiviBundle:Etude')->findBy(array('stateID' => STATE_ID_EN_COURS), array('mandat' => 'DESC', 'num' => 'DESC'));
        $etudesTerminees = $em->getRepository('mgateSuiviBundle:Etude')->findBy(array('stateID' => STATE_ID_TERMINEE), array('mandat' => 'DESC', 'num' => 'DESC'));
        $etudes = array_merge($etudesEnCours, $etudesTerminees);

        $chartManager = $this->get('mgate.chart_manager');
        $ob = $chartManager->getGanttSuivi($etudes);

        return $this->render('mgateSuiviBundle:Etude:suiviQualite.html.twig', array(
            'etudesEnCours' => $etudesEnCours,
            'etudesTerminees' => $etudesTerminees,
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function suiviUpdateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $etude = $em->getRepository('mgateSuiviBundle:Etude')->find($id);

        if (!$etude) {
            throw $this->createNotFoundException('Unable to find Etude entity.');
        }

        if ($this->get('mgate.etude_manager')->confidentielRefus($etude, $this->getUser(), $this->get('security.authorization_checker'))) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $formSuivi = $this->createForm(new SuiviEtudeType(), $etude);
        if ($this->get('request')->getMethod() == 'POST') {
            $formSuivi->handleRequest($this->get('request'));

            if ($formSuivi->isValid()) {
                $em->persist($etude);
                $em->flush();

                $return = array('responseCode' => 100, 'msg' => 'ok');
            } else {
                $return = array('responseCode' => 200, 'msg' => 'Erreur:'.$formSuivi->getErrorsAsString());
            }
        }

        return new JsonResponse($return); //make sure it has the correct content type
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function vuCAAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id > 0) {
            $etude = $em->getRepository('mgateSuiviBundle:Etude')->find($id);
        } else {
            $etude = $em->getRepository('mgateSuiviBundle:Etude')->findOneBy(array('stateID' => STATE_ID_EN_COURS));
        }

        if (!$etude) {
            throw $this->createNotFoundException('Unable to find Etude entity.');
        }

        //Etudes En Négociation : stateID = 1
        $etudesDisplayList = $em->getRepository('mgateSuiviBundle:Etude')->getTwoStates([STATE_ID_EN_NEGOCIATION, STATE_ID_EN_COURS], array('mandat' => 'ASC', 'num' => 'ASC'));

        if (!in_array($etude, $etudesDisplayList)) {
            throw $this->createNotFoundException('Etude incorrecte');
        }

        /* pagination management */
        $currentEtudeId = array_search($etude, $etudesDisplayList);
        $nextId = min(count($etudesDisplayList), $currentEtudeId + 1);
        $previousId = max(0, $currentEtudeId - 1);

        $chartManager = $this->get('mgate.chart_manager');
        $ob = $chartManager->getGantt($etude, 'suivi');

        return $this->render('mgateSuiviBundle:Etude:vuCA.html.twig', array(
            'etude' => $etude,
            'chart' => $ob,
            'nextID' => $etudesDisplayList[$nextId]->getId(),
            'prevID' => $etudesDisplayList[$previousId]->getId(),
            'etudesDisplayList' => $etudesDisplayList,
        ));
    }
}
