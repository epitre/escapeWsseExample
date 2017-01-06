<?php

namespace Tests\ExampleWsseApiBundle\Controller;

use AppBundle\Entity\Company;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CompanyControllerTest extends ExampleWsseApiTestCase
{
    public function testGet()
    {
        // add all your fixtures classes that implement
        // Doctrine\Common\DataFixtures\FixtureInterface
        $this->loadFixtures(array(
            'AppBundle\DataFixtures\ORM\LoadCompanyData',
            'AppBundle\DataFixtures\ORM\LoadUserData',
        ));

        // On s'assure que l'api n'est pas accessible sans authentification
        $this->client->request(
            'GET',
            '/v1/companies'
        );
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        $username = 'test';
        $wsseHeader = $this->getWsseHeader($username);
        $this->client->request(
            'GET',
            '/v1/companies',
            array(),
            array(),
            array('HTTP_X-WSSE' => $wsseHeader)
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $wsseHeader = $this->getWsseHeader($username);
        $jsonResponse = $this->client->getResponse()->getContent();
        $response = json_decode($jsonResponse);
        $this->client->request(
            'GET',
            '/v1/companies/'.$response[0]->id,
            array(),
            array(),
            array('HTTP_X-WSSE' => $wsseHeader)
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testPost()
    {
        // add all your fixtures classes that implement
        // Doctrine\Common\DataFixtures\FixtureInterface
        $this->loadFixtures(array('AppBundle\DataFixtures\ORM\LoadUserData'));

        // On s'assure que l'api n'est pas accessible sans authentification
        $this->client->request('POST', '/v1/companies');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        $username = 'test';
        $wsseHeader = $this->getWsseHeader($username);
        $this->client->request(
            'POST',
            '/v1/companies',
            array(),
            array(),
            array('HTTP_X-WSSE' => $wsseHeader)
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);

        $company = new Company();
        $company->setName("myformidableCompany");
        $serializer = SerializerBuilder::create()->build();
        $array = $serializer->toArray($company);
        $wsseHeader = $this->getWsseHeader($username);
        $this->client->request(
            'POST',
            '/v1/companies',
            array('company' => $array),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-WSSE' => $wsseHeader
            )
        );
        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);

        // Ici, on ne remplit pas company
        $company = new Company();
        $serializer = SerializerBuilder::create()->build();
        $array = $serializer->toArray($company);
        $wsseHeader = $this->getWsseHeader($username);
        $this->client->request(
            'POST',
            '/v1/companies',
            array('company' => $array),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-WSSE' => $wsseHeader
            )
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
//        $this->assertValidationErrors(['data.email', 'data.message'], $this->client->getContainer());

        // Try again with with the fields filled out.
//        $form = $crawler->selectButton('Submit')->form();
//        $form->setValues(['contact[email]' => 'nobody@example.com', 'contact[message]' => 'Hello']);
//        $this->client->submit($form);
//        $this->assertStatusCode(302, $this->client);
    }

    public function testPut()
    {
        // On s'assure que l'api n'est pas accessible sans authentification
        $this->client->request('PUT', '/v1/companies/1234');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // add all your fixtures classes that implement
        // Doctrine\Common\DataFixtures\FixtureInterface
        $this->loadFixtures(array(
            'AppBundle\DataFixtures\ORM\LoadCompanyData',
            'AppBundle\DataFixtures\ORM\LoadUserData'
        ));

        /**
         * Le $user qui interroge l'API
         */
        $username = 'test';
        $society = 'cimes';
        /**
         * @var $company Company
         */
        $company = $this->em->createQuery(
                'SELECT c FROM AppBundle:Company c WHERE c.name LIKE :registration_for_like'
            )
            ->setParameter('registration_for_like', $society)
            ->getOneOrNullResult();
        Assert::assertNotNull($company, 'The society '.$society.' has not been loaded into the DB.');
        // On s'assure d'abord que si rien n'est modifié, alors l'API le voit bien.
        $serializer = SerializerBuilder::create()->build();
        $array = $serializer->toArray($company);
        // on n'envoie pas l'id
        unset($array['id']);
        $wsseHeader = $this->getWsseHeader($username);
        $this->client->request(
            'PUT',
            '/v1/companies/'.$company->getId(),
            array('company' => $array),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-WSSE' => $wsseHeader
            )
        );
        $this->assertStatusCode(Response::HTTP_NOT_MODIFIED, $this->client);

        $newSociety = 'Monts';
        $result = $this->em->createQuery(
            'SELECT c FROM AppBundle:Company c WHERE c.name LIKE :registration_for_like'
        )
            ->setParameter('registration_for_like', $newSociety)
            ->getOneOrNullResult();
        // We make sure the new society name does not already exist
        Assert::assertNull($result, 'The society '.$newSociety.' was already loaded into the DB.');

        $company->setName($newSociety);
        $serializer = SerializerBuilder::create()->build();
        $array = $serializer->toArray($company);
        // on n'envoie pas l'id
        unset($array['id']);
        $wsseHeader = $this->getWsseHeader($username);
        $this->client->request(
            'PUT',
            '/v1/companies/'.$company->getId(),
            array('company' => $array),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-WSSE' => $wsseHeader
            )
        );
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);
    }
}
