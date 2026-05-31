<?php

declare(strict_types=1);

use PedalPal\Repository\FileRepository;

/**
 * Repository for accessories data (JSON flat file).
 *
 * Accessories include items like Water Bottle, Bike Light, etc.
 * Compatible with any bike type via the CompatibleWith field.
 */
class AccessoryRepository extends FileRepository
{
    public function __construct(string $dataFolder, ?\PedalPal\Cache\CacheInterface $cache = null)
    {
        parent::__construct($dataFolder, 'accessories.json', $cache);
    }

    /** @return list<array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>}> */
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

        /** @var list<array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>}> $decoded */
        return $decoded;
    }

    /** @param list<array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>}> $data */
    protected function writeToSource(array $data): void
    {
        file_put_contents(
            $this->dataPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
