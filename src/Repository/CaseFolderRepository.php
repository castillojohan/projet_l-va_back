<?php

namespace App\Repository;

use App\Entity\CaseFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaseFolder>
 *
 * @method CaseFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseFolder[]    findAll()
 * @method CaseFolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseFolder::class);
    }

    public function add(CaseFolder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CaseFolder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get all cases status processed
     */
    public function findProcessedCases()
    {
        return $this->createQueryBuilder('c')
        ->where('c.status LIKE :status')
        ->setParameter('status', '%'.'PROCESSED'.'%')
        ->getQuery()
        ->getResult();
    }

    /**
     * Get all cases status Ongoing
     */
    public function findOngoingCases()
    {
        return $this->createQueryBuilder('c')
        ->where('c.status LIKE :status')
        ->setParameter('status', '%'.'ONGOING'.'%')
        ->getQuery()
        ->getResult();
    }
    
    /**
     * Pagination QB, offset = page, limit= nbr of result
     */
    public function findAllWithPagination($offset, $limit)
    {
        $qb = $this->createQueryBuilder('u')
            ->setFirstResult(($offset -1)* $limit)
            ->setMaxResults($limit)
            ->addOrderBy('u.status', 'ASC')
            ->addOrderBy('u.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
