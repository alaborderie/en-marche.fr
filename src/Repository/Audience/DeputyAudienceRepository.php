<?php

namespace App\Repository\Audience;

use App\Entity\Audience\DeputyAudience;
use Doctrine\Persistence\ManagerRegistry;

class DeputyAudienceRepository extends AudienceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeputyAudience::class);
    }
}
