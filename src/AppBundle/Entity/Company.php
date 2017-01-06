<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CompanyRepository")
 * @ORM\Table(name="company")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ExclusionPolicy("all")
 */
class Company
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string $id uniq company id
     *
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=100, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "A company name must be at least {{ limit }} characters long",
     *      maxMessage = "A company name cannot be longer than {{ limit }} characters"
     * )
     *
     * @var string $name prénom du profil
     *
     * @Type("string")
     * @Expose
     */
    private $name;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Assert\Date()
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Assert\Date()
     * @Assert\GreaterThanOrEqual("today")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Company
     */
    public function setName($name): Company
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Company
     */
    public function setCreatedAt($createdAt) : Company
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Company
     */
    public function setUpdatedAt($updatedAt) : Company
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * because of the symfony forms, we have to check if this is a Company acceptable for a contact
     *
     * @return bool
     */
    public function hasValues() :bool
    {
        return !is_null($this->name) || !is_null($this->id);
    }

    /**
     * tests to see if a company is the same
     *
     * @param Company $company
     * @return bool
     */
    public function equals(Company $company) :bool
    {
        return $company->getName() == $this->getName();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (empty($this->createdAt)) {
            $this->setCreatedAt(new \DateTime());
        }
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }
}
