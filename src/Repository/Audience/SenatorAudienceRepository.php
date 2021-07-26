<?php

namespace App\Repository\Audience;

use App\Entity\Audience\SenatorAudience;
use Doctrine\Persistence\ManagerRegistry;

class SenatorAudienceRepository extends AudienceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SenatorAudience::class);
    }
}
