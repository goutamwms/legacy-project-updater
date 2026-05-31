<?php

declare(strict_types=1);

use PedalPal\Repository\FileRepository;

/**
 * Repository for mountain bikes (JSON flat file).
 *
 * Mountain bikes use PascalCase keys (BikeID, ModelName, etc.)
 * and include terrain, suspension, and weight-specific fields.
 */
class MountainBikeRepository extends FileRepository
{
    public function __construct(string $dataFolder, ?\PedalPal\Cache\CacheInterface $cache = null)
    {
        parent::__construct($dataFolder, 'mountain_bikes.json', $cache);
    }

    /** @return list<array{BikeID: int, ModelName: string, Brand: string, GearCount: int, SuspensionType: string, FrameMaterial: string, DailyRate: float, IsAvailable: bool, Terrain: string, WeightKg: float}> */
    protected function loadFromSource(): array
    {
        $contents = @file_get_contents($this->dataPath);
        if ($contents === false) {
            return [];
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decoded) || !array_is_list($decoded)) {
            return [];
        }

        /** @var list<array{BikeID: int, ModelName: string, Brand: string, GearCount: int, SuspensionType: string, FrameMaterial: string, DailyRate: float, IsAvailable: bool, Terrain: string, WeightKg: float}> $decoded */
        return $decoded;
    }

    /** @param list<array{BikeID: int, ModelName: string, Brand: string, GearCount: int, SuspensionType: string, FrameMaterial: string, DailyRate: float, IsAvailable: bool, Terrain: string, WeightKg: float}> $data */
    protected function writeToSource(array $data): void
    {
        file_put_contents(
            $this->dataPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
