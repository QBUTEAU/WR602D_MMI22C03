<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function countUserFilesToday(User $user): int
    {
        return $this->createQueryBuilder('f')
        ->select('COUNT(f.id)')
        ->where('f.user = :user')
        ->andWhere('f.createdAt >= :today')
        ->setParameter('user', $user)
        ->setParameter('today', (new DateTime())->setTime(0, 0))
        ->getQuery()
        ->getSingleScalarResult();
    }
}
