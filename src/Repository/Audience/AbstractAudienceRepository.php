<?php

namespace App\Repository\Audience;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractAudienceRepository extends ServiceEntityRepository
{
    public function findByZones(array $zones): array
    {
        $qb = $this->createQueryBuilder('audience');

        return $qb
            ->innerJoin('audience.zone', 'zone')
            ->leftJoin('zone.parents', 'parent')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('zone.id', ':zones'),
                    $qb->expr()->in('parent.id', ':zones'),
                )
            )
            ->setParameter(':zones', $zones)
            ->getQuery()
            ->getResult()
        ;
    }
}
