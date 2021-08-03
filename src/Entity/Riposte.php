<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={
 *         "access_control": "is_granted('ROLE_ADHERENT')",
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/ripostes",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/ripostes/{id}",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *     }
 * )
 */
class Riposte
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     */
    private $sourceUrl;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $withNotification;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $enabled;

    public function __construct(UuidInterface $uuid = null, $withNotification = true, $enabled = true)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->withNotification = $withNotification;
        $this->enabled = $enabled;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): void
    {
        $this->sourceUrl = $sourceUrl;
    }

    public function isWithNotification(): bool
    {
        return $this->withNotification;
    }

    public function setWithNotification(bool $withNotification): void
    {
        $this->withNotification = $withNotification;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
