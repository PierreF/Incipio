<?php

namespace mgate\PubliBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class ImportController extends Controller
{


    const AVAILABLE_FORMATS = array('Siaje Etudes');

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response Display an upload form for a csv resources from other crm.
     * Display an upload form for a csv resources from other crm.
     */
    public function indexAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder(array())->add('import_method', ChoiceType::class, array('label' => 'Type du fichier',
                'required' => true,
                'choices' => $this::AVAILABLE_FORMATS,
                'expanded' => true,
                'multiple' => false)
        )
            ->add('file', FileType::class, array('label' => 'Fichier', 'required' => true, 'attr' => array('cols' => '100%', 'rows' => 5)))->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('import_method')->getData() == 'Siaje Etudes') {
                $data = $form->getData();

                // Création d'un fichier temporaire
                $file = $data['file'];

                $siajeImporter = $this->get('mgate.import.siaje_etude');

               $results = $siajeImporter->run($file);

                $request->getSession()->getFlashBag()->add('success', 'Document importé. '.$results['inserted_projects'].' études créées, '.$results['inserted_prospects'].' prospects créés');
                return $this->redirect($this->generateUrl('mgate_publi_import'));
            }
        }

        return $this->render('mgatePubliBundle:Import:index.html.twig', array('form' => $form->createView()));
    }


    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @param $number integer id of service as stated in $this::AVAILABLE_FORMATS
     * Return an html snippet of how csv should be formatted to match import
     * @return JsonResponse an array containing expected headers.
     */
    public function ajaxExpectedFormatAction($service_number)
    {
        if($service_number < count($this::AVAILABLE_FORMATS)){
            if($this::AVAILABLE_FORMATS[$service_number] == 'Siaje Etudes') {
                $siajeImporter = $this->get('mgate.import.siaje_etude');
                return new JsonResponse($siajeImporter->expectedFormat());
            }
        }

        return new JsonResponse(null);

    }


}
