<?php

declare(strict_types=1);

namespace PedalPal\Service;

/**
 * Contract for bike-type-specific services.
 *
 * Decouples the HTTP handlers from the concrete repository and
 * business logic, allowing any bike type to be added by registering
 * a new implementation in BikeServiceRegistry.
 */
interface BikeServiceInterface
{
    /** @return list<array<string, mixed>> */
    public function getAll(): array;
    public function rentBike(int $bikeId): bool;
    public function resetToDefaults(): void;
}
