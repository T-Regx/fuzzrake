<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\DateTime\UtcClock;
use App\Utils\StringList;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="events")
 */
class Event
{
    final public const TYPE_DATA_UPDATED = 'DATA_UPDATED';
    final public const TYPE_CS_UPDATED = 'CS_UPDATED';
    final public const TYPE_GENERIC = 'GENERIC';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $timestamp;

    /**
     * @ORM\Column(type="string", length=4095)
     */
    private string $description = '';

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $type = self::TYPE_DATA_UPDATED;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $noLongerOpenFor = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $nowOpenFor = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $trackingIssues = false;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $artisanName = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $checkedUrls = '';

    /**
     * @ORM\Column(type="integer")
     */
    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    private int $newMakersCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    private int $updatedMakersCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    #[GreaterThanOrEqual(value: 0)]
    #[LessThan(value: 500)]
    private int $reportedUpdatedMakersCount = 0;

    /**
     * @ORM\Column(type="string", length=256)
     */
    #[Length(max: 256)]
    private string $gitCommits = '';

    public function __construct()
    {
        $this->timestamp = UtcClock::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeImmutable $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNoLongerOpenFor(): string
    {
        return $this->noLongerOpenFor;
    }

    /**
     * @return string[]
     */
    public function getNoLongerOpenForArray(): array
    {
        return StringList::unpack($this->noLongerOpenFor);
    }

    public function setNoLongerOpenFor(string $noLongerOpenFor): self
    {
        $this->noLongerOpenFor = $noLongerOpenFor;

        return $this;
    }

    public function getNowOpenFor(): string
    {
        return $this->nowOpenFor;
    }

    /**
     * @return string[]
     */
    public function getNowOpenForArray(): array
    {
        return StringList::unpack($this->nowOpenFor);
    }

    public function setNowOpenFor(string $nowOpenFor): self
    {
        $this->nowOpenFor = $nowOpenFor;

        return $this;
    }

    public function getTrackingIssues(): bool
    {
        return $this->trackingIssues;
    }

    public function setTrackingIssues(bool $trackingIssues): self
    {
        $this->trackingIssues = $trackingIssues;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getArtisanName(): string
    {
        return $this->artisanName;
    }

    public function setArtisanName(string $artisanName): self
    {
        $this->artisanName = $artisanName;

        return $this;
    }

    public function getCheckedUrls(): string
    {
        return $this->checkedUrls;
    }

    /**
     * @return string[]
     */
    public function getCheckedUrlsArray(): array
    {
        return StringList::unpack($this->checkedUrls);
    }

    public function setCheckedUrls(string $checkedUrls): self
    {
        $this->checkedUrls = $checkedUrls;

        return $this;
    }

    public function getNewMakersCount(): int
    {
        return $this->newMakersCount;
    }

    public function setNewMakersCount(int $newMakersCount): self
    {
        $this->newMakersCount = $newMakersCount;

        return $this;
    }

    public function getUpdatedMakersCount(): int
    {
        return $this->updatedMakersCount;
    }

    public function setUpdatedMakersCount(int $updatedMakersCount): self
    {
        $this->updatedMakersCount = $updatedMakersCount;

        return $this;
    }

    public function getReportedUpdatedMakersCount(): int
    {
        return $this->reportedUpdatedMakersCount;
    }

    public function setReportedUpdatedMakersCount(int $reportedUpdatedMakersCount): self
    {
        $this->reportedUpdatedMakersCount = $reportedUpdatedMakersCount;

        return $this;
    }

    public function getGitCommits(): string
    {
        return $this->gitCommits;
    }

    public function setGitCommits(string $gitCommits): self
    {
        $this->gitCommits = $gitCommits;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getGitCommitsArray(): array
    {
        return StringList::unpack($this->gitCommits);
    }

    public function isTypeCsUpdated(): bool
    {
        return self::TYPE_CS_UPDATED == $this->type;
    }

    public function isTypeDataUpdated(): bool
    {
        return self::TYPE_DATA_UPDATED === $this->type;
    }

    public function isEditable(): bool
    {
        return in_array($this->type, [self::TYPE_GENERIC, self::TYPE_DATA_UPDATED]);
    }
}
