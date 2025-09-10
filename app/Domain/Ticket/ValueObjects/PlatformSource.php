<?php declare(strict_types=1);

namespace App\Domain\Ticket\ValueObjects;

use InvalidArgumentException;
use Stringable;

use function in_array;
use function sprintf;

final readonly class PlatformSource implements Stringable
{
    private const VALID_PLATFORMS = [
        'TICKETMASTER',
        'STUBHUB',
        'VIAGOGO',
        'SEETICKETS',
        'TICKETEK',
        'EVENTIM',
        'FOOTBALL_CLUB_STORES',
        'OFFICIAL_VENUE',
        'OTHER',
    ];

    public function __construct(
        private string $platform,
        private ?string $url = NULL,
    ) {
        $this->validate($platform);
    }

    /**
     * Platform
     */
    public function platform(): string
    {
        return strtoupper($this->platform);
    }

    /**
     * Url
     */
    public function url(): ?string
    {
        return $this->url;
    }

    /**
     * DisplayName
     */
    public function displayName(): string
    {
        return match ($this->platform()) {
            'TICKETMASTER'         => 'Ticketmaster',
            'STUBHUB'              => 'StubHub',
            'VIAGOGO'              => 'viagogo',
            'SEETICKETS'           => 'See Tickets',
            'TICKETEK'             => 'Ticketek',
            'EVENTIM'              => 'Eventim',
            'FOOTBALL_CLUB_STORES' => 'Football Club Stores',
            'OFFICIAL_VENUE'       => 'Official Venue',
            'OTHER'                => 'Other',
            default                => ucfirst(strtolower($this->platform)),
        };
    }

    /**
     * Check if  official
     */
    public function isOfficial(): bool
    {
        return in_array($this->platform(), [
            'TICKETMASTER',
            'SEETICKETS',
            'TICKETEK',
            'EVENTIM',
            'FOOTBALL_CLUB_STORES',
            'OFFICIAL_VENUE',
        ], TRUE);
    }

    /**
     * Check if  reseller
     */
    public function isReseller(): bool
    {
        return in_array($this->platform(), [
            'STUBHUB',
            'VIAGOGO',
        ], TRUE);
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return $this->platform() === $other->platform()
               && $this->url === $other->url;
    }

    /**
     * @return array<int, string>
     */
    /**
     * ValidPlatforms
     */
    public static function validPlatforms(): array
    {
        return self::VALID_PLATFORMS;
    }

    /**
     * Ticketmaster
     */
    public static function ticketmaster(?string $url = NULL): self
    {
        return new self('TICKETMASTER', $url);
    }

    /**
     * StubHub
     */
    public static function stubHub(?string $url = NULL): self
    {
        return new self('STUBHUB', $url);
    }

    /**
     * Viagogo
     */
    public static function viagogo(?string $url = NULL): self
    {
        return new self('VIAGOGO', $url);
    }

    /**
     * SeeTickets
     */
    public static function seeTickets(?string $url = NULL): self
    {
        return new self('SEETICKETS', $url);
    }

    /**
     * OfficialVenue
     */
    public static function officialVenue(?string $url = NULL): self
    {
        return new self('OFFICIAL_VENUE', $url);
    }

    /**
     * FromString
     */
    public static function fromString(string $platform, ?string $url = NULL): self
    {
        return new self($platform, $url);
    }

    /**
     * Validate
     */
    private function validate(string $platform): void
    {
        if (in_array(trim($platform), ['', '0'], TRUE)) {
            throw new InvalidArgumentException('Platform source cannot be empty');
        }

        $normalizedPlatform = strtoupper(trim($platform));
        if (! in_array($normalizedPlatform, self::VALID_PLATFORMS, TRUE)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid platform source: %s. Valid platforms: %s',
                    $platform,
                    implode(', ', self::VALID_PLATFORMS),
                ),
            );
        }
    }

    /**
     * __toString
     */
    public function __toString(): string
    {
        return $this->displayName();
    }
}