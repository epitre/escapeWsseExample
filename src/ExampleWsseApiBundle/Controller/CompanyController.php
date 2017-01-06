<?php

namespace ExampleWsseApiBundle\Controller;

use AppBundle\Entity\Company;
use AppBundle\Form\CompanyType;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerBuilder;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("Company", pluralize=true)
 */
class CompanyController extends Controller
{
    /**
     * This method gets all the profiles for certain registration if needed
     *
     * @ApiDoc(
     *  section="Companys",
     *  resource=true,
     *  description="This method returns all the companys",
     *  filters={
     *      {"name"="registration_before_at", "dataType"="date"},
     *      {"name"="registration_after_at", "dataType"="date"},
     *      {"name"="registration_for_like", "dataType"="string"}
     *  },
     *  tags={
     *    "done" = "#00ff00"
     *  }
     * )
     *
     * @param $request Request
     * @return \AppBundle\Entity\Company[]
     */
    public function cgetAction(Request $request)
    {
        $registrationBefore = $request->query->get('registration_before');
        $registrationAfter = $request->query->get('registration_after');
        $registrationForLike = $request->query->get('registration_for_like');
        if (empty($registrationBefore) && empty($registrationAfter) && empty($registrationForLike)) {
            return $this->getDoctrine()->getManager()->getRepository('AppBundle:Company')->findAll();
        }
        return $this->getDoctrine()->getManager()->getRepository('AppBundle:Company')->findCompanyByDateAndName($registrationBefore, $registrationAfter, $registrationForLike);
    } // "cget_user"     [GET] /companys

    /**
     * This method gets only one specific company
     *
     * @ApiDoc(
     *  section="Companys",
     *  resource=true,
     *  description="This method returns a specific company",
     *  tags={
     *    "done" = "#00ff00"
     *  }
     * )
     *
     * @param $id int identifiant d'un company
     * @return mixed
     */
    public function getAction($id)
    {
        $company = $this->getDoctrine()->getManager()->getRepository('AppBundle:Company')->find($id);
        if (!$company instanceof Company) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent('Company '.$id.' not found.');
            return $response;
        }
        return $company;
    } // "get_user"      [GET] /companys/{id}

    /**
     * This method creates a new company
     *
     * @ApiDoc(
     *  section="Companys",
     *  resource=true,
     *  description="This method creates a new company",
     *  input="AppBundle\Form\CompanyType",
     *  output="AppBundle\Entity\Company",
     *  tags={
     *    "done" = "#00ff00"
     *  }
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postAction(Request $request)
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($company);
            $em->flush();

            //Display just created company
            return View::create($company, Response::HTTP_CREATED);
        }

        //Display form errors
        return View::create($form, Response::HTTP_BAD_REQUEST);
    } // "post_profile"      [POST] /companys

    /**
     * This method updates a company
     *
     * @ApiDoc(
     *  section="Companys",
     *  resource=true,
     *  description="This method updates or creates a company",
     *  input="AppBundle\Form\CompanyType",
     *  output="AppBundle\Entity\Company",
     *  tags={
     *    "done" = "#00ff00"
     *  }
     * )
     *
     * @param $id int identifiant d'un company
     * @param Request $request
     *
     * @return mixed
     */
    public function putAction($id, Request $request)
    {
        $company = $this->getDoctrine()->getManager()->getRepository('AppBundle:Company')->find($id);
        if (!$company instanceof Company) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent('Company '.$id.' not found.');
            return $response;
        }
        // On vérifie que quelque chose à changer
        $serializer = SerializerBuilder::create()->build();
        $object = $request->get('company');
        $newCompany = $serializer->fromArray($object, ' AppBundle\Entity\Company');
        if ($company->equals($newCompany)) {
            // On répond que rien n'a changé
            return View::create($company, Response::HTTP_NOT_MODIFIED);
        }
        // on met à jour la date de modification de company
        $company->setUpdatedAt(new \DateTime());
        $form = $this->createForm(CompanyType::class, $company);
        $form->submit($request->get('company'), false);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($company);
            $em->flush();

            //Display just updated company
            return View::create($company, Response::HTTP_NO_CONTENT);
        }

        //Display form errors
        return View::create($form, Response::HTTP_BAD_REQUEST);
    } // "put_company"             [PUT] /companys/{id}
}
