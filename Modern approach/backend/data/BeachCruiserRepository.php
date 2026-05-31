<?php

declare(strict_types=1);

use PedalPal\Repository\FileRepository;

/**
 * Repository for beach cruiser bikes (XML flat file).
 *
 * Beach cruisers have snake_case keys (bike_id, model_name, etc.)
 * and are stored in XML format to demonstrate mixed-format support.
 */
class BeachCruiserRepository extends FileRepository
{
    public function __construct(string $dataFolder, ?\PedalPal\Cache\CacheInterface $cache = null)
    {
        parent::__construct($dataFolder, 'beach_cruisers.xml', $cache);
    }

    /** @return list<array{bike_id: int, model_name: string, color: string, frame_size: string, daily_rate: float, is_available: bool}> */
    protected function loadFromSource(): array
    {
        $xml = @simplexml_load_file($this->dataPath);
        if ($xml === false) {
            return [];
        }

        $bikes = [];
        foreach ($xml->Bike as $bikeNode) {
            $bikes[] = [
                'bike_id'      => intval((string)$bikeNode->bike_id),
                'model_name'   => (string)$bikeNode->model_name,
                'color'        => (string)$bikeNode->color,
                'frame_size'   => (string)$bikeNode->frame_size,
                'daily_rate'   => floatval((string)$bikeNode->daily_rate),
                'is_available' => ((string)$bikeNode->is_available === 'true'),
            ];
        }

        return $bikes;
    }

    /** @param list<array{bike_id: int, model_name: string, color: string, frame_size: string, daily_rate: float, is_available: bool}> $data */
    protected function writeToSource(array $data): void
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><BeachCruisers/>');

        foreach ($data as $bike) {
            $bikeNode = $xml->addChild('Bike');
            $bikeNode->addChild('bike_id', (string)$bike['bike_id']);
            $bikeNode->addChild('model_name', htmlspecialchars($bike['model_name']));
            $bikeNode->addChild('color', htmlspecialchars($bike['color']));
            $bikeNode->addChild('frame_size', htmlspecialchars($bike['frame_size']));
            $bikeNode->addChild('daily_rate', (string)$bike['daily_rate']);
            $bikeNode->addChild('is_available', $bike['is_available'] ? 'true' : 'false');
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        if ($dom === null) {
            return;
        }
        $dom->formatOutput = true;
        $dom->save($this->dataPath);
    }
}
