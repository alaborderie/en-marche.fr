<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Geo\Zone;
use App\Validator\ValidScope;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AudienceFilter extends AbstractAdherentFilter
{
    use GeneralFilterTrait;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"audience_segment_write"})
     */
    private $includeAdherentsNoCommittee = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"audience_segment_write"})
     */
    private $includeAdherentsInCommittee = true;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     */
    protected $isCertified;

    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     */
    private $zone;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     choices=App\Scope\ScopeEnum::FOR_AUDIENCE_SEGMENT,
     *     message="audience_segment.scope.invalid_choice",
     *     strict=true
     * )
     * @ValidScope
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     */
    private $scope;

    public function includeAdherentsNoCommittee(): ?bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function setIncludeAdherentsNoCommittee(?bool $value): void
    {
        $this->includeAdherentsNoCommittee = $value;
    }

    public function includeAdherentsInCommittee(): ?bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function setIncludeAdherentsInCommittee(?bool $value): void
    {
        $this->includeAdherentsInCommittee = $value;
    }

    /**
     * @Groups({"audience_segment_read"})
     */
    public function getIsCommitteeMember(): ?bool
    {
        return $this->includeAdherentsInCommittee ? true : ($this->includeAdherentsNoCommittee ? false : null);
    }

    public function isCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }
}
