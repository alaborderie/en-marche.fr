<?php

namespace AppBundle\Entity\Election;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\City;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="election_city_card", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="city_card_city_unique", columns="city_id")
 * })
 *
 * @UniqueEntity("city")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CityCard
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $population;

    /**
     * @var City
     *
     * @ORM\OneToOne(targetEntity=City::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $city;

    /**
     * @var CityCandidate|null
     *
     * @ORM\OneToOne(targetEntity=CityCandidate::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $firstCandidate;

    /**
     * @var CityManager|null
     *
     * @ORM\OneToOne(targetEntity=CityManager::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $headquartersManager;

    /**
     * @var CityManager|null
     *
     * @ORM\OneToOne(targetEntity=CityManager::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $politicManager;

    /**
     * @var CityManager|null
     *
     * @ORM\OneToOne(targetEntity=CityManager::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $taskForceManager;

    /**
     * @var CityPrevision|null
     *
     * @ORM\OneToOne(targetEntity=CityPrevision::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $preparationPrevision;

    /**
     * @var CityPrevision|null
     *
     * @ORM\OneToOne(targetEntity=CityPrevision::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $candidatePrevision;

    /**
     * @var CityPrevision|null
     *
     * @ORM\OneToOne(targetEntity=CityPrevision::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Assert\Valid
     */
    private $nationalPrevision;

    /**
     * @var CityPartner[]|Collection
     *
     * @ORM\OneToMany(targetEntity=CityPartner::class, mappedBy="city", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $partners;

    /**
     * @var CityContact[]|Collection
     *
     * @ORM\OneToMany(targetEntity=CityContact::class, mappedBy="city", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $contacts;

    public function __construct(City $city = null, ?int $population = null)
    {
        $this->city = $city;
        $this->population = $population;
        $this->partners = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function __toString()
    {
        if (!$this->city) {
            return '';
        }

        return sprintf('%s (%s)', $this->city->getName(), $this->city->getInseeCode());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getFirstCandidate(): ?CityCandidate
    {
        return $this->firstCandidate;
    }

    public function setFirstCandidate(?CityCandidate $firstCandidate): void
    {
        $this->firstCandidate = $firstCandidate;
    }

    public function removeFirstCandidate(): void
    {
        $this->firstCandidate = null;
    }

    public function getHeadquartersManager(): ?CityManager
    {
        return $this->headquartersManager;
    }

    public function setHeadquartersManager(?CityManager $headquartersManager): void
    {
        $this->headquartersManager = $headquartersManager;
    }

    public function removeHeadquartersManager(): void
    {
        $this->headquartersManager = null;
    }

    public function getPoliticManager(): ?CityManager
    {
        return $this->politicManager;
    }

    public function setPoliticManager(?CityManager $politicManager): void
    {
        $this->politicManager = $politicManager;
    }

    public function removePoliticManager(): void
    {
        $this->politicManager = null;
    }

    public function getTaskForceManager(): ?CityManager
    {
        return $this->taskForceManager;
    }

    public function setTaskForceManager(?CityManager $taskForceManager): void
    {
        $this->taskForceManager = $taskForceManager;
    }

    public function removeTaskForceManager(): void
    {
        $this->taskForceManager = null;
    }

    public function getPreparationPrevision(): ?CityPrevision
    {
        return $this->preparationPrevision;
    }

    public function setPreparationPrevision(?CityPrevision $preparationPrevision): void
    {
        $this->preparationPrevision = $preparationPrevision;
    }

    public function removePreparationPrevision(): void
    {
        $this->preparationPrevision = null;
    }

    public function getCandidatePrevision(): ?CityPrevision
    {
        return $this->candidatePrevision;
    }

    public function setCandidatePrevision(?CityPrevision $candidatePrevision): void
    {
        $this->candidatePrevision = $candidatePrevision;
    }

    public function removeCandidatePrevision(): void
    {
        $this->candidatePrevision = null;
    }

    public function getNationalPrevision(): ?CityPrevision
    {
        return $this->nationalPrevision;
    }

    public function setNationalPrevision(?CityPrevision $nationalPrevision): void
    {
        $this->nationalPrevision = $nationalPrevision;
    }

    public function removeNationalPrevision(): void
    {
        $this->nationalPrevision = null;
    }

    public function getPartners(): Collection
    {
        return $this->partners;
    }

    public function addPartner(CityPartner $partner): void
    {
        if (!$this->partners->contains($partner)) {
            $partner->setCity($this);
            $this->partners->add($partner);
        }
    }

    public function removePartner(CityPartner $partner): void
    {
        $this->partners->removeElement($partner);
    }

    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(CityContact $contact): void
    {
        if (!$this->contacts->contains($contact)) {
            $contact->setCity($this);
            $this->contacts->add($contact);
        }
    }

    public function removeContact(CityContact $contact): void
    {
        $this->contacts->removeElement($contact);
    }
}
