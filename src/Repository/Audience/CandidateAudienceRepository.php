<?php

namespace App\Repository\Audience;

use App\Entity\Audience\CandidateAudience;
use Doctrine\Persistence\ManagerRegistry;

class CandidateAudienceRepository extends AudienceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateAudience::class);
    }
}
