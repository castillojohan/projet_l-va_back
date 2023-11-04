<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * QueryBuilder to get only admin users, for users chat with admins
     *
     */
    public function findUsersAdmin()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%'.'ROLE_ADMIN'.'%')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithPagination($offset, $limit)
    {
        $qb = $this->createQueryBuilder('u')
            ->setFirstResult(($offset -1)* $limit)
            ->setMaxResults($limit)
            ->orderBy('u.id', 'DESC');
        return $qb->getQuery()->getResult();
    }



    public function findByUserPseudo($searchTerm)
    { 

    return $this->createQueryBuilder('u')
                ->where('u.pseudo LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.$searchTerm.'%')
                ->getQuery()
                ->getResult();
    }
}
