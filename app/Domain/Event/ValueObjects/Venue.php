<?php declare(strict_types=1);

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

use function sprintf;
use function strlen;

final readonly class Venue
{
    public function __construct(
        private string $name,
        private string $city,
        private string $country,
        private ?string $address = NULL,
        private ?int $capacity = NULL,
    ) {
        $this->validate($name, $city, $country, $capacity);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function capacity(): ?int
    {
        return $this->capacity;
    }

    public function fullName(): string
    {
        return sprintf('%s, %s, %s', $this->name, $this->city, $this->country);
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name
               && $this->city === $other->city
               && $this->country === $other->country
               && $this->address === $other->address
               && $this->capacity === $other->capacity;
    }

    public static function create(
        string $name,
        string $city,
        string $country,
        ?string $address = NULL,
        ?int $capacity = NULL,
    ): self {
        return new self($name, $city, $country, $address, $capacity);
    }

    private function validate(string $name, string $city, string $country, ?int $capacity): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Venue name cannot be empty');
        }

        if (empty(trim($city))) {
            throw new InvalidArgumentException('Venue city cannot be empty');
        }

        if (empty(trim($country))) {
            throw new InvalidArgumentException('Venue country cannot be empty');
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Venue name cannot exceed 255 characters');
        }

        if (strlen($city) > 100) {
            throw new InvalidArgumentException('Venue city cannot exceed 100 characters');
        }

        if (strlen($country) > 100) {
            throw new InvalidArgumentException('Venue country cannot exceed 100 characters');
        }

        if ($capacity !== NULL && $capacity <= 0) {
            throw new InvalidArgumentException('Venue capacity must be positive');
        }

        if ($capacity !== NULL && $capacity > 200000) {
            throw new InvalidArgumentException('Venue capacity seems unrealistic (max 200,000)');
        }
    }

    public function __toString(): string
    {
        return $this->fullName();
    }
}
