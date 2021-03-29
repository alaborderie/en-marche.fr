<?php

namespace App\Mailer\Message;

use App\Entity\VotingPlatform\Vote;
use Ramsey\Uuid\Uuid;

final class VotingPlatformElectionVoteConfirmationMessage extends AbstractVotingPlatformMessage
{
    public static function create(Vote $vote, string $voterKey): self
    {
        $adherent = $vote->getVoter()->getAdherent();
        $election = $vote->getElection();

        return new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('[%s] Félicitations, vos bulletins sont dans l\'urne !', self::getMailSubjectPrefix($election->getDesignation())),
            [
                'first_name' => $adherent->getFirstName(),
                'voter_key' => static::escape($voterKey),
                'election_type' => $election->getDesignationType(),
                'vote_end_date' => static::formatDate($election->getRealVoteEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
            ],
        );
    }
}
