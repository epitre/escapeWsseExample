<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CompanyRepository
 *
 */
class CompanyRepository extends EntityRepository
{
    /**
     * @param string|\DateTime|null $registrationBefore
     * @param string|\DateTime|null $registrationAfter
     * @param string|null $registrationForLike
     * @return array
     */
    public function findCompanyByDateAndName($registrationBefore, $registrationAfter, $registrationForLike): array
    {
        if (empty($registrationBefore)) {
            $registrationBefore = new \DateTime('now');
        } elseif (!$registrationBefore instanceof \DateTime) {
            $registrationBefore = new \DateTime($registrationBefore);
        }
        if (empty($registrationAfter)) {
            $registrationAfter = new \DateTime('1971-01-01');
        } elseif (!$registrationAfter instanceof \DateTime) {
            $registrationAfter = new \DateTime($registrationAfter);
        }
        if (empty($registrationForLike)) {
            $registrationForLike = '%';
        } else {
            $registrationForLike = '%' . $registrationForLike . '%';
        }
        $result = $this->getEntityManager()
            ->createQuery(
                'SELECT c FROM AppBundle:Company c WHERE c.createdAt >= :registration_after AND c.createdAt <= :registration_before AND (c.name LIKE :registration_for_like)'
            )
            ->setParameter('registration_before', $registrationBefore)
            ->setParameter('registration_after', $registrationAfter)
            ->setParameter('registration_for_like', $registrationForLike)
            ->getResult();
        return $result;
    }
}
