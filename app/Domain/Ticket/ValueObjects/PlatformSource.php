<?php declare(strict_types=1);

namespace App\Domain\Ticket\ValueObjects;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final readonly class PlatformSource
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

    public function platform(): string
    {
        return strtoupper($this->platform);
    }

    public function url(): ?string
    {
        return $this->url;
    }

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

    public function isReseller(): bool
    {
        return in_array($this->platform(), [
            'STUBHUB',
            'VIAGOGO',
        ], TRUE);
    }

    public function equals(self $other): bool
    {
        return $this->platform() === $other->platform()
               && $this->url === $other->url;
    }

    public static function validPlatforms(): array
    {
        return self::VALID_PLATFORMS;
    }

    public static function ticketmaster(?string $url = NULL): self
    {
        return new self('TICKETMASTER', $url);
    }

    public static function stubHub(?string $url = NULL): self
    {
        return new self('STUBHUB', $url);
    }

    public static function viagogo(?string $url = NULL): self
    {
        return new self('VIAGOGO', $url);
    }

    public static function seeTickets(?string $url = NULL): self
    {
        return new self('SEETICKETS', $url);
    }

    public static function officialVenue(?string $url = NULL): self
    {
        return new self('OFFICIAL_VENUE', $url);
    }

    public static function fromString(string $platform, ?string $url = NULL): self
    {
        return new self($platform, $url);
    }

    private function validate(string $platform): void
    {
        if (empty(trim($platform))) {
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

    public function __toString(): string
    {
        return $this->displayName();
    }
}
