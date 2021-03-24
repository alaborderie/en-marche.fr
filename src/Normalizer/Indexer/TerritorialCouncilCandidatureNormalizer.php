<?php

namespace App\Normalizer\Indexer;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;

class TerritorialCouncilCandidatureNormalizer extends AbstractDesignationCandidatureNormalizer
{
    protected function getClassName(): string
    {
        return Candidacy::class;
    }

    protected function normalizeElectionEntity(CandidacyInterface $candidacy): array
    {
        /** @var Election $election */
        $election = $candidacy->getElection();
        $coTerr = $election->getTerritorialCouncil();

        return [
            'id' => $coTerr->getId(),
            'territorial_council_id' => $coTerr->getId(),
            'name' => $coTerr->getName(),
        ];
    }

    /**
     * @param Candidacy $object
     */
    protected function normalizeCustomFields(CandidacyInterface $object): array
    {
        if ($object->hasOtherCandidacies()) {
            $ids = array_map(function (Candidacy $candidacy) {
                return $candidacy->getId();
            }, $object->getCandidaciesGroup()->getCandidacies());
        }

        return [
            'project' => $object->getFaithStatement(),
            'binome_ids' => $ids ?? null,
        ];
    }
}
