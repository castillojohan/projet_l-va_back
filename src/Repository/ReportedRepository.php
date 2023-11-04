<?php

namespace App\Repository;

use App\Entity\Reported;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reported>
 *
 * @method Reported|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reported|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reported[]    findAll()
 * @method Reported[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reported::class);
    }

    public function add(Reported $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reported $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithPagination($offset, $limit)
    {
        $qb = $this->createQueryBuilder('u')
            ->setFirstResult(($offset -1)* $limit)
            ->setMaxResults($limit)
            ->addOrderBy('u.reportedNumber', 'DESC');

        return $qb->getQuery()
                  ->getResult();
    }


    public function findByReportedPseudo($searchTerm)
    {

    return $this->createQueryBuilder('r')
                ->where('r.reportedPseudo LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.$searchTerm.'%')
                ->getQuery()
                ->getResult();
    }
}
