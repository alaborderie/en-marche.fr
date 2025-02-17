<?php

namespace App\Mailchimp;

use App\AdherentMessage\DynamicSegmentInterface;
use App\Coalition\CoalitionMemberValueObject;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\MailchimpSegment;
use App\Mailchimp\Campaign\CampaignContentRequestBuilder;
use App\Mailchimp\Campaign\CampaignRequestBuilder;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Event\CampaignEvent;
use App\Mailchimp\Event\RequestEvent;
use App\Mailchimp\Exception\InvalidCampaignIdException;
use App\Mailchimp\MailchimpSegment\SegmentRequestBuilder;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommandInterface;
use App\Mailchimp\Synchronisation\MemberRequest\CoalitionMemberRequestBuilder;
use App\Mailchimp\Synchronisation\MemberRequest\NewsletterMemberRequestBuilder;
use App\Mailchimp\Synchronisation\RequestBuilder;
use App\Newsletter\NewsletterValueObject;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Manager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const INTEREST_KEY_COMMITTEE_HOST = 'COMMITTEE_HOST';
    public const INTEREST_KEY_COMMITTEE_PROVISIONAL_SUPERVISOR = 'COMMITTEE_PROVISIONAL_SUPERVISOR';
    public const INTEREST_KEY_COMMITTEE_SUPERVISOR = 'COMMITTEE_SUPERVISOR';
    public const INTEREST_KEY_COMMITTEE_FOLLOWER = 'COMMITTEE_FOLLOWER';
    public const INTEREST_KEY_COMMITTEE_NO_FOLLOWER = 'COMMITTEE_NO_FOLLOWER';
    public const INTEREST_KEY_REFERENT = 'REFERENT';
    public const INTEREST_KEY_DEPUTY = 'DEPUTY';
    public const INTEREST_KEY_REC = 'REC';
    public const INTEREST_KEY_COORDINATOR = 'COORDINATOR';
    public const INTEREST_KEY_PROCURATION_MANAGER = 'PROCURATION_MANAGER';
    public const INTEREST_KEY_ASSESSOR_MANAGER = 'ASSESSOR_MANAGER';
    public const INTEREST_KEY_BOARD_MEMBER = 'BOARD_MEMBER';

    private $driver;
    private $requestBuildersLocator;
    private $eventDispatcher;
    private $mailchimpObjectIdMapping;

    public function __construct(
        Driver $driver,
        ContainerInterface $requestBuildersLocator,
        EventDispatcherInterface $eventDispatcher,
        MailchimpObjectIdMapping $mailchimpObjectIdMapping
    ) {
        $this->driver = $driver;
        $this->requestBuildersLocator = $requestBuildersLocator;
        $this->eventDispatcher = $eventDispatcher;
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    /**
     * Creates/updates a Mailchimp member
     */
    public function editMember(Adherent $adherent, AdherentChangeCommandInterface $message): void
    {
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromAdherent($adherent)
        ;

        $result = $this->driver->editMember(
            $requestBuilder->buildMemberRequest($message->getEmailAddress()),
            $this->mailchimpObjectIdMapping->getMainListId()
        );

        if ($result) {
            $this->updateMemberTags(
                $message->getEmailAddress(),
                $this->mailchimpObjectIdMapping->getMainListId(),
                $requestBuilder
            );
        }
    }

    public function editNewsletterMember(NewsletterValueObject $newsletter): void
    {
        $listId = $this->mailchimpObjectIdMapping->getNewsletterListId();
        $requestBuilder = $this->requestBuildersLocator->get(NewsletterMemberRequestBuilder::class);

        $result = $this->driver->editMember(
            $requestBuilder
                ->updateFromValueObject($newsletter)
                ->build($newsletter->getEmail()),
            $listId
        );

        if ($result) {
            $request = $requestBuilder->createMemberTagsRequest($newsletter->getEmail());

            if ($request->hasTags()) {
                // Active/Inactive member's tags
                $this->driver->updateMemberTags($request, $listId);
            }
        }
    }

    public function editElectedRepresentativeMember(
        ElectedRepresentative $electedRepresentative,
        ElectedRepresentativeChangeCommandInterface $message
    ): void {
        $emailAddress = $electedRepresentative->getEmailAddress();
        $listId = $this->mailchimpObjectIdMapping->getElectedRepresentativeListId();

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromElectedRepresentative($electedRepresentative)
        ;

        $result = $this->driver->editMember(
            $requestBuilder->buildMemberRequest($message->getOldEmailAddress() ?? $emailAddress),
            $listId
        );

        if ($result) {
            $this->updateMemberTags($emailAddress, $listId, $requestBuilder);
        }
    }

    public function editApplicationRequestCandidate(ApplicationRequest $applicationRequest): void
    {
        $requestBuilder = $this->requestBuildersLocator->get(RequestBuilder::class);

        $result = $this
            ->driver
            ->editMember(
                $requestBuilder
                    ->updateFromApplicationRequest($applicationRequest)
                    ->buildMemberRequest($applicationRequest->getEmailAddress()),
                $this->mailchimpObjectIdMapping->getApplicationRequestCandidateListId()
            )
        ;

        if ($result) {
            // Active/Inactive member's tags
            $this->driver->updateMemberTags(
                $requestBuilder->createMemberTagsRequest($applicationRequest->getEmailAddress()),
                $this->mailchimpObjectIdMapping->getApplicationRequestCandidateListId()
            );
        }
    }

    public function editJecouteContact(DataSurvey $dataSurvey, array $zones): void
    {
        $emailAddress = $dataSurvey->getEmailAddress();
        $listId = $this->mailchimpObjectIdMapping->getJecouteListId();

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator
            ->get(RequestBuilder::class)
            ->updateFromDataSurvey($dataSurvey, $zones)
        ;

        $this->driver->editMember(
            $requestBuilder->buildMemberRequest($emailAddress),
            $listId
        );
    }

    public function editCoalitionMember(CoalitionMemberValueObject $contact): void
    {
        $listId = $this->mailchimpObjectIdMapping->getCoalitionsListId();
        $requestBuilder = $this->requestBuildersLocator->get(CoalitionMemberRequestBuilder::class);
        $email = $contact->getEmail();

        $result = $this->driver->editMember(
            $requestBuilder
                ->updateFromValueObject($contact)
                ->build($email),
            $listId
        );

        if ($result) {
            $currentTags = $this->driver->getMemberTags($email, $listId);

            $this->driver->updateMemberTags(
                $requestBuilder->createMemberTagsRequest($email, $currentTags),
                $listId
            );
        }
    }

    public function getCampaignContent(MailchimpCampaign $campaign): string
    {
        if (!$campaign->getExternalId()) {
            throw new InvalidCampaignIdException(sprintf('Message "%s" does not have a valid campaign id', $campaign->getMessage()->getUuid()));
        }

        return $this->driver->getCampaignContent($campaign->getExternalId());
    }

    public function editCampaign(MailchimpCampaign $campaign): bool
    {
        $message = $campaign->getMessage();

        $this->eventDispatcher->dispatch(new CampaignEvent($campaign), Events::CAMPAIGN_FILTERS_PRE_BUILD);

        /** @var CampaignRequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator->get(CampaignRequestBuilder::class);

        $editCampaignRequest = $requestBuilder->createEditCampaignRequestFromMessage($campaign);

        $this->eventDispatcher->dispatch(new RequestEvent($message, $editCampaignRequest), Events::CAMPAIGN_PRE_EDIT);

        // When ExternalId does not exist, then it is Campaign creation
        if (!$campaignId = $campaign->getExternalId()) {
            $campaignData = $this->driver->createCampaign($editCampaignRequest);

            if (empty($campaignData['id'])) {
                throw new \RuntimeException(sprintf('Campaign for the message "%s" has not been created', $message->getUuid()));
            }

            $campaign->setExternalId($campaignData['id']);
        } else {
            $campaignData = $this->driver->updateCampaign($campaignId, $editCampaignRequest);
        }

        if (isset($campaignData['recipients']['recipient_count'])) {
            $campaign->setRecipientCount($campaignData['recipients']['recipient_count']);
        }

        return true;
    }

    public function editCampaignContent(MailchimpCampaign $campaign): bool
    {
        $this->checkMessageExternalId($campaign);

        /** @var CampaignContentRequestBuilder $contentRequestBuilder */
        $contentRequestBuilder = $this->requestBuildersLocator->get(CampaignContentRequestBuilder::class);

        if (!$this->driver->editCampaignContent(
            $campaign->getExternalId(),
            $contentRequestBuilder->createContentRequest($message = $campaign->getMessage())
        )) {
            $this->logger->warning(
                sprintf('Campaign content of "%s" message has not been modified', $message->getUuid()->toString())
            );

            return false;
        }

        return true;
    }

    public function deleteCampaign(string $campaignId): void
    {
        if (!$this->driver->deleteCampaign($campaignId)) {
            $this->logger->warning(sprintf('Campaign "%s" has not be deleted', $campaignId));
        }
    }

    public function sendCampaign(AdherentMessageInterface $message): bool
    {
        foreach ($message->getMailchimpCampaigns() as $campaign) {
            $this->checkMessageExternalId($campaign);
        }

        $globalStatus = false;

        foreach ($message->getMailchimpCampaigns() as $campaign) {
            $success = $this->driver->sendCampaign($campaign->getExternalId());

            $globalStatus |= $success;

            $success ? $campaign->markAsSent() : $campaign->markAsError($this->driver->getLastError());
        }

        return $globalStatus;
    }

    public function sendTestCampaign(AdherentMessageInterface $message, array $emails): bool
    {
        $campaign = current($message->getMailchimpCampaigns());

        $this->checkMessageExternalId($campaign);

        return $this->driver->sendTestCampaign($campaign->getExternalId(), $emails);
    }

    public function createStaticSegment(string $name, string $listId = null): ?int
    {
        $listId = $listId ?? $this->mailchimpObjectIdMapping->getMainListId();
        $response = $this->driver->createStaticSegment($name, $listId);

        $responseData = $this->driver->toArray($response);

        if (200 === $response->getStatusCode()) {
            return $responseData['id'] ?? null;
        }

        // Search segment id in existing segments
        if (400 === $response->getStatusCode() && isset($responseData['detail']) && 'Sorry, that tag already exists.' === $responseData['detail']) {
            return $this->findSegmentId($name, $listId);
        }

        return null;
    }

    public function createDynamicSegment(DynamicSegmentInterface $segment, string $listId = null): bool
    {
        return $this->editDynamicSegment($segment, null, $listId);
    }

    public function updateDynamicSegment(DynamicSegmentInterface $segment, int $segmentId, string $listId = null): bool
    {
        return $this->editDynamicSegment($segment, $segmentId, $listId);
    }

    private function editDynamicSegment(
        DynamicSegmentInterface $segment,
        int $segmentId = null,
        string $listId = null
    ): bool {
        /** @var SegmentRequestBuilder $requestBuilder */
        $requestBuilder = $this->requestBuildersLocator->get(SegmentRequestBuilder::class);

        $listId = $listId ?? $this->mailchimpObjectIdMapping->getMainListId();
        if ($segmentId) {
            $response = $this->driver->updateDynamicSegment($segmentId, $listId, $requestBuilder->createEditSegmentRequestFromDynamicSegment($segment));
        } else {
            $response = $this->driver->createDynamicSegment($listId, $requestBuilder->createEditSegmentRequestFromDynamicSegment($segment));
        }

        $responseData = $this->driver->toArray($response);

        if (200 === $response->getStatusCode()) {
            $segment->setMailchimpId($responseData['id']);
            $segment->setRecipientCount($responseData['member_count']);
            $segment->setSynchronized(true);

            return true;
        }

        return false;
    }

    public function findSegmentId(string $name, string $listId): ?int
    {
        $offset = 0;
        $limit = 1000;

        while ($segments = $this->driver->getSegments($listId, $offset, $limit)) {
            foreach ($segments as $segment) {
                if ($segment['name'] === $name) {
                    return $segment['id'];
                }
            }

            $offset += \count($segments);
        }

        return null;
    }

    public function deleteStaticSegment(int $id): bool
    {
        return $this->driver->deleteStaticSegment($id);
    }

    public function addMemberToStaticSegment(int $segmentId, string $mail): void
    {
        $this->driver->pushSegmentMember($segmentId, $mail);
    }

    public function removeMemberFromStaticSegment(int $segmentId, string $mail): void
    {
        $this->driver->deleteSegmentMember($segmentId, $mail);
    }

    public function deleteMember(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getMainListId());
    }

    public function archiveElectedRepresentative(string $mail): void
    {
        $this->driver->archiveMember($mail, $this->mailchimpObjectIdMapping->getElectedRepresentativeListId());
    }

    public function deleteElectedRepresentative(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getElectedRepresentativeListId());
    }

    public function deleteNewsletterMember(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getNewsletterListId());
    }

    public function deleteCoalitionMember(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getCoalitionsListId());
    }

    public function deleteApplicationRequestCandidate(string $mail): void
    {
        $this->driver->deleteMember($mail, $this->mailchimpObjectIdMapping->getApplicationRequestCandidateListId());
    }

    public function getReportData(MailchimpCampaign $campaign): array
    {
        $this->checkMessageExternalId($campaign);

        return $this->driver->getReportData($campaign->getExternalId());
    }

    private function checkMessageExternalId(MailchimpCampaign $campaign): void
    {
        if (!$campaign->getExternalId()) {
            throw new InvalidCampaignIdException(sprintf('Message "%s" does not have a valid campaign id', $campaign->getMessage()->getUuid()->toString()));
        }
    }

    private function updateMemberTags(string $emailAddress, string $listId, RequestBuilder $requestBuilder): void
    {
        $currentTags = $this->driver->getMemberTags($emailAddress, $listId);

        $this->driver->updateMemberTags(
            $requestBuilder->createMemberTagsRequest($emailAddress, $currentTags),
            $listId
        );
    }

    public function createMailchimpSegment(MailchimpSegment $mailchimpSegment): ?int
    {
        $listId = MailchimpSegment::LIST_MAIN === $mailchimpSegment->getList()
            ? $this->mailchimpObjectIdMapping->getMainListId()
            : $this->mailchimpObjectIdMapping->getElectedRepresentativeListId()
        ;

        return $this->createStaticSegment($mailchimpSegment->getLabel(), $listId);
    }
}
