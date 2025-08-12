<?php declare(strict_types=1);

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final readonly class SportCategory
{
    private const VALID_CATEGORIES = [
        'FOOTBALL',
        'BASKETBALL',
        'TENNIS',
        'CRICKET',
        'RUGBY',
        'BASEBALL',
        'AMERICAN_FOOTBALL',
        'ICE_HOCKEY',
        'GOLF',
        'MOTORSPORT',
        'BOXING',
        'MMA',
        'CYCLING',
        'ATHLETICS',
        'SWIMMING',
        'GYMNASTICS',
        'OTHER',
    ];

    public function __construct(
        private string $category,
    ) {
        $this->validate($category);
    }

    public function value(): string
    {
        return strtoupper($this->category);
    }

    public function displayName(): string
    {
        return match ($this->value()) {
            'AMERICAN_FOOTBALL' => 'American Football',
            'ICE_HOCKEY'        => 'Ice Hockey',
            'MMA'               => 'Mixed Martial Arts',
            default             => ucfirst(strtolower($this->category)),
        };
    }

    public function isTeamSport(): bool
    {
        return in_array($this->value(), [
            'FOOTBALL',
            'BASKETBALL',
            'CRICKET',
            'RUGBY',
            'BASEBALL',
            'AMERICAN_FOOTBALL',
            'ICE_HOCKEY',
        ], TRUE);
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    /**
     * @return array<string, string>
     */
    public static function validCategories(): array
    {
        return array_combine(
            self::VALID_CATEGORIES,
            array_map(
                fn (string $category) => match ($category) {
                    'AMERICAN_FOOTBALL' => 'American Football',
                    'ICE_HOCKEY'        => 'Ice Hockey',
                    'MMA'               => 'Mixed Martial Arts',
                    default             => ucfirst(strtolower($category)),
                },
                self::VALID_CATEGORIES,
            ),
        );
    }

    public static function fromString(string $category): self
    {
        return new self($category);
    }

    public static function football(): self
    {
        return new self('FOOTBALL');
    }

    public static function basketball(): self
    {
        return new self('BASKETBALL');
    }

    public static function tennis(): self
    {
        return new self('TENNIS');
    }

    private function validate(string $category): void
    {
        if (empty(trim($category))) {
            throw new InvalidArgumentException('Sport category cannot be empty');
        }

        $normalizedCategory = strtoupper(trim($category));
        if (! in_array($normalizedCategory, self::VALID_CATEGORIES, TRUE)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid sport category: %s. Valid categories: %s',
                    $category,
                    implode(', ', self::VALID_CATEGORIES),
                ),
            );
        }
    }

    public function __toString(): string
    {
        return $this->displayName();
    }
}
