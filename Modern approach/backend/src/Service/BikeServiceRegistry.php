<?php

declare(strict_types=1);

namespace PedalPal\Service;

/**
 * Registry of bike-type services.
 *
 * Implements the Registry pattern so that adding a new bike type
 * requires only one call to register() – no switch statements,
 * no hard-coded property maps.
 */
class BikeServiceRegistry
{
    /** @var array<string, BikeServiceInterface> */
    private array $services = [];

    /** Register a service for a given bike type key (e.g. "beach", "mountain"). */
    public function register(string $type, BikeServiceInterface $service): void
    {
        $this->services[$type] = $service;
    }

    /** Look up a service by type key. Returns null for unregistered types. */
    public function get(string $type): ?BikeServiceInterface
    {
        return $this->services[$type] ?? null;
    }

    /** @return array<string, BikeServiceInterface> */
    public function getAll(): array
    {
        return $this->services;
    }
}
