<?php

declare(strict_types=1);

namespace PedalPal\Service;

use PedalPal\Repository\FileRepository;

/**
 * Generic bike service implementation.
 *
 * Works with any FileRepository by accepting the key names for
 * the bike ID and availability flag plus an optional list of
 * default records for reset functionality.
 *
 * This is the concrete strategy used by BikeServiceRegistry for
 * each registered bike type.
 */
class BikeService implements BikeServiceInterface
{
    private FileRepository $repo;
    private string $idKey;
    private string $availableKey;
    /** @var list<array<string, mixed>> */
    private array $defaults;

    /**
     * @param list<array<string, mixed>> $defaults
     */
    public function __construct(
        FileRepository $repo,
        string $idKey = 'bike_id',
        string $availableKey = 'is_available',
        array $defaults = []
    ) {
        $this->repo         = $repo;
        $this->idKey        = $idKey;
        $this->availableKey = $availableKey;
        $this->defaults     = $defaults;
    }

    /** @return list<array<string, mixed>> */
    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    /**
     * Mark a bike as rented by setting its availability flag to false.
     * Returns false if the bike is already rented or does not exist.
     */
    public function rentBike(int $bikeId): bool
    {
        $bikes = $this->repo->getAll();

        foreach ($bikes as &$bike) {
            if ($bike[$this->idKey] === $bikeId) {
                if (!$bike[$this->availableKey]) {
                    return false;
                }
                $bike[$this->availableKey] = false;
                $this->repo->save($bikes);

                return true;
            }
        }
        unset($bike);

        return false;
    }

    /** Restore data to the defaults provided at construction time. */
    public function resetToDefaults(): void
    {
        if ($this->defaults !== []) {
            $this->repo->save($this->defaults);
        }
    }
}
