<?php

namespace App\Repository\Audience;

use App\Entity\Audience\AbstractAudience;
use App\Entity\Audience\AudienceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AudienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractAudience::class);
    }

    public function findByUuid(string $uuid): AudienceInterface
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

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
