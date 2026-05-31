<?php

declare(strict_types=1);

use PedalPal\Repository\FileRepository;

/**
 * Repository for electric bikes (JSON flat file).
 *
 * Electric bikes have fields for battery range, motor power,
 * charge time, and weight in addition to the standard fields.
 * Added as a demonstration of the extensible registry pattern.
 */
class ElectricBikeRepository extends FileRepository
{
    public function __construct(string $dataFolder, ?\PedalPal\Cache\CacheInterface $cache = null)
    {
        parent::__construct($dataFolder, 'electric_bikes.json', $cache);
    }

    /** @return list<array{bike_id: int, model_name: string, brand: string, battery_range_km: int, motor_power_w: int, daily_rate: float, is_available: bool, weight_kg: float, charge_time_h: float}> */
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

        /** @var list<array{bike_id: int, model_name: string, brand: string, battery_range_km: int, motor_power_w: int, daily_rate: float, is_available: bool, weight_kg: float, charge_time_h: float}> $decoded */
        return $decoded;
    }

    /** @param list<array{bike_id: int, model_name: string, brand: string, battery_range_km: int, motor_power_w: int, daily_rate: float, is_available: bool, weight_kg: float, charge_time_h: float}> $data */
    protected function writeToSource(array $data): void
    {
        file_put_contents(
            $this->dataPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
