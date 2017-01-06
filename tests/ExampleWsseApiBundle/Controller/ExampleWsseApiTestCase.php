<?php

namespace Tests\ExampleWsseApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class ExampleWsseApiTestCase extends WebTestCase
{
    /**
     * @var $em EntityManager
     */
    protected $em;
    /**
     * @var $encoder UserPasswordEncoder
     */
    protected $encoder;
    /**
     * @var $client Client
     */
    protected $client;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        // add all your fixtures classes that implement
        // Doctrine\Common\DataFixtures\FixtureInterface
        // On a au moins besoin des user pour l'authentification
        $this->loadFixtures(array(
            'AppBundle\DataFixtures\ORM\LoadUserData',
        ));
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->encoder = static::$kernel->getContainer()->get('security.password_encoder');
        $this->client = static::createClient(array(
            'environment' => 'test',
//            'debug'       => false,
        ));
    }

    /**
     * @param $username string
     *
     * @return UserInterface
     */
    protected function getUser($username)
    {
        /**
         * @var $userManager UserManager
         */
        $userManager = static::$kernel->getContainer()->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        return $user;
    }

    /**
     * @param $username string
     *
     * @return string
     */
    protected function getWsseHeader($username)
    {
        $user = $this->getUser($username);
        $password = $user->getPassword();
        $nonce = uniqid();
        $created = new \DateTime('now', new \DateTimezone('UTC'));
        $created = $created->format(\DateTime::ISO8601);

        $digest = base64_encode(sha1($nonce . $created . $password, true));

        return "UsernameToken Username=\"$username\", PasswordDigest=\"$digest\", Nonce=\"" . base64_encode($nonce) . "\", Created=\"$created\"";
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }
}
