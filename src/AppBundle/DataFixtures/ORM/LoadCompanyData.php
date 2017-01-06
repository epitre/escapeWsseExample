<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Company;

class LoadCompanyData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $company1 = new Company();
        $company1->setName("Cimes");
        $company1->setCreatedAt(new \DateTime("2016-06-15"));
        $manager->persist($company1);

        $company2 = new Company();
        $company2->setName("Defac");
        $company2->setCreatedAt(new \DateTime("2016-09-10"));
        $manager->persist($company2);

        $manager->flush();

        $this->addReference('company1', $company1);
        $this->addReference('company2', $company2);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}
