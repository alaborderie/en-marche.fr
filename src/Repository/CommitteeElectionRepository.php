<?php

namespace App\Repository;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionEntity;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CommitteeElectionRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeElection::class);
    }

    /**
     * @return CommitteeElection[]
     */
    public function findAllByDesignation(Designation $designation, int $offset = 0, ?int $limit = 200): array
    {
        return $this->createQueryBuilder('ce')
            ->addSelect('c')
            ->innerJoin('ce.committee', 'c')
            ->where('ce.designation = :designation')
            ->andWhere('c.status = :approved')
            ->setParameters([
                'designation' => $designation,
                'approved' => Committee::APPROVED,
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CommitteeElection[]
     */
    public function findAllToNotify(Designation $designation, int $offset = 0, ?int $limit = 200): array
    {
        return $this->createQueryBuilder('ce')
            ->addSelect('c')
            ->innerJoin('ce.committee', 'c')
            ->innerJoin('ce.candidacies', 'candidacy')
            ->where('ce.designation = :designation')
            ->andWhere('ce.adherentNotified = :false')
            ->setParameters([
                'designation' => $designation,
                'false' => false,
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findElections(
        CommitteeDesignationsListFilter $filter,
        int $page = 1,
        int $limit = 50
    ): PaginatorInterface {
        $qb = $this
            ->createQueryBuilder('committee_election')
            ->addSelect('committee', 'designation')
            ->addSelect(sprintf('(%s) AS total_confirmed_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(sub_committee_candidacy_1.id IS NOT NULL AND sub_committee_candidacy_1.gender = :male, 1, 0))')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_1')
                    ->where('sub_committee_candidacy_1.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_1.status = :confirmed')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_confirmed_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(sub_committee_candidacy_2.id IS NOT NULL AND sub_committee_candidacy_2.gender = :female, 1, 0))')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_2')
                    ->where('sub_committee_candidacy_2.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_2.status = :confirmed')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_draft_candidacy_male',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(sub_committee_candidacy_3.id IS NOT NULL AND sub_committee_candidacy_3.gender = :male, 1, 0))')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_3')
                    ->where('sub_committee_candidacy_3.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_3.status = :draft')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS total_draft_candidacy_female',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('SUM(IF(sub_committee_candidacy_4.id IS NOT NULL AND sub_committee_candidacy_4.gender = :female, 1, 0))')
                    ->from(CommitteeCandidacy::class, 'sub_committee_candidacy_4')
                    ->where('sub_committee_candidacy_4.committeeElection = committee_election')
                    ->andWhere('sub_committee_candidacy_4.status = :draft')
                    ->getDQL()
            ))
            ->addSelect(sprintf('(%s) AS winners',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('GROUP_CONCAT(CONCAT_WS(\'|\', sub_voting_platform_candidate.gender, sub_voting_platform_candidate.firstName, sub_voting_platform_candidate.lastName))')
                    ->from(Candidate::class, 'sub_voting_platform_candidate')
                    ->innerJoin('sub_voting_platform_candidate.candidateGroup', 'sub_candidate_group', Join::WITH, 'sub_candidate_group.elected = :true')
                    ->innerJoin('sub_candidate_group.electionPool', 'sub_election_pool')
                    ->innerJoin('sub_election_pool.election', 'sub_voting_platform_election')
                    ->innerJoin('sub_voting_platform_election.electionEntity', 'sub_voting_platform_election_entity')
                    ->andWhere('sub_voting_platform_election_entity.committee = committee')
                    ->andWhere('sub_voting_platform_election.designation = designation')
                    ->getDQL()
            ))
            ->addSelect('voting_platform_election.uuid as voting_platform_election_uuid')
            ->innerJoin('committee_election.committee', 'committee')
            ->innerJoin('committee_election.designation', 'designation')
            ->leftJoin(ElectionEntity::class, 'voting_platform_election_entity', Join::WITH, 'voting_platform_election_entity.committee = committee')
            ->leftJoin('voting_platform_election_entity.election', 'voting_platform_election')
            ->where('committee.status = :approved')
            ->andWhere('voting_platform_election.designation = designation')
            ->setParameters([
                'approved' => Committee::APPROVED,
                'male' => Genders::MALE,
                'female' => Genders::FEMALE,
                'confirmed' => CandidacyInterface::STATUS_CONFIRMED,
                'draft' => CandidacyInterface::STATUS_DRAFT,
                'true' => true,
            ])
            ->orderBy('designation.voteStartDate', 'DESC')
        ;

        if ($filter->getCommitteeName()) {
            $qb
                ->andWhere('committee.name LIKE :committee_name')
                ->setParameter('committee_name', '%'.$filter->getCommitteeName().'%')
            ;
        }

        if ($filter->getZones()) {
            $this->withGeoZones(
                $filter->getZones(),
                $qb,
                'committee',
                Committee::class,
                'c2',
                'zones',
                'z2',
                function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                    $zoneQueryBuilder->andWhere(sprintf('%s.status = :approved', $entityClassAlias));
                }
            );
        }

        return $this->configurePaginator($qb, $page, $limit);
    }
}
