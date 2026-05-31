<?php

declare(strict_types=1);

use PedalPal\Cache\CacheInterface;
use PedalPal\Config;
use PedalPal\Service\BikeService;
use PedalPal\Service\BikeServiceInterface;
use PedalPal\Service\BikeServiceRegistry;

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../data/BeachCruiserRepository.php';
require_once __DIR__ . '/../data/MountainBikeRepository.php';
require_once __DIR__ . '/../data/ElectricBikeRepository.php';
require_once __DIR__ . '/../data/AccessoryRepository.php';
require_once __DIR__ . '/AccessoryService.php';

/**
 * Service-locator / composition root for the application.
 *
 * Initialises all repositories, registers each bike type into
 * the BikeServiceRegistry, and provides static accessors for
 * the HTTP handlers to consume.
 */
class ApplicationServices
{
    private static ?BikeServiceRegistry $bikeRegistry = null;
    private static ?AccessoryService $accessoryService = null;
    private static ?CacheInterface $cache = null;

    /**
     * Bootstrap all application services.
     *
     * Loads .env if available, resolves the cache adapter, creates
     * repositories, and populates the BikeServiceRegistry with all
     * supported bike types (beach, mountain, electric).
     *
     * Safe to call multiple times – subsequent calls re-initialise.
     */
    public static function initialize(string $dataFolder, ?CacheInterface $cache = null): void
    {
        $dotenvPath = __DIR__ . '/../';
        if (class_exists('\Dotenv\Dotenv') && file_exists($dotenvPath . '.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($dotenvPath);
            $dotenv->safeLoad();
        }

        self::$cache = $cache ?? Config::cache();

        $registry = new BikeServiceRegistry();

        $beachRepo     = new BeachCruiserRepository($dataFolder, self::$cache);
        $mountainRepo  = new MountainBikeRepository($dataFolder, self::$cache);
        $electricRepo  = new ElectricBikeRepository($dataFolder, self::$cache);
        $accessoryRepo = new AccessoryRepository($dataFolder, self::$cache);

        $registry->register('beach', new BikeService($beachRepo, 'bike_id', 'is_available', [
            ['bike_id' => 1, 'model_name' => 'Sunset Drifter',  'color' => 'Coral',      'frame_size' => 'Medium', 'daily_rate' => 14.99, 'is_available' => true],
            ['bike_id' => 2, 'model_name' => 'Ocean Breeze',    'color' => 'Teal',       'frame_size' => 'Large',  'daily_rate' => 16.99, 'is_available' => true],
            ['bike_id' => 3, 'model_name' => 'Sandy Shores',    'color' => 'Cream',      'frame_size' => 'Small',  'daily_rate' => 12.99, 'is_available' => false],
            ['bike_id' => 4, 'model_name' => 'Tropical Wave',   'color' => 'Lime Green', 'frame_size' => 'Medium', 'daily_rate' => 15.99, 'is_available' => true],
            ['bike_id' => 5, 'model_name' => 'Breezy Blue',     'color' => 'Sky Blue',   'frame_size' => 'Large',  'daily_rate' => 17.99, 'is_available' => true],
            ['bike_id' => 6, 'model_name' => 'Flamingo Glide',  'color' => 'Hot Pink',   'frame_size' => 'Small',  'daily_rate' => 13.99, 'is_available' => false],
        ]));

        $registry->register('mountain', new BikeService($mountainRepo, 'BikeID', 'IsAvailable', [
            ['BikeID' => 101, 'ModelName' => 'TrailBlazer X9',   'Brand' => 'ApexRide',   'GearCount' => 21, 'SuspensionType' => 'Full',     'FrameMaterial' => 'Aluminum',     'DailyRate' => 24.99, 'IsAvailable' => true,  'Terrain' => 'All-Mountain',  'WeightKg' => 13.5],
            ['BikeID' => 102, 'ModelName' => 'Summit Shredder',  'Brand' => 'PeakForce',  'GearCount' => 27, 'SuspensionType' => 'Full',     'FrameMaterial' => 'Carbon Fiber', 'DailyRate' => 34.99, 'IsAvailable' => true,  'Terrain' => 'Enduro',        'WeightKg' => 11.2],
            ['BikeID' => 103, 'ModelName' => 'Canyon Crusher',   'Brand' => 'TerraRide',  'GearCount' => 18, 'SuspensionType' => 'Hardtail', 'FrameMaterial' => 'Steel',        'DailyRate' => 19.99, 'IsAvailable' => false, 'Terrain' => 'Cross-Country', 'WeightKg' => 14.8],
            ['BikeID' => 104, 'ModelName' => 'Ridge Runner',     'Brand' => 'ApexRide',   'GearCount' => 24, 'SuspensionType' => 'Hardtail', 'FrameMaterial' => 'Aluminum',     'DailyRate' => 22.99, 'IsAvailable' => true,  'Terrain' => 'Trail',         'WeightKg' => 12.9],
            ['BikeID' => 105, 'ModelName' => 'Peak Predator',    'Brand' => 'SummitX',    'GearCount' => 30, 'SuspensionType' => 'Full',     'FrameMaterial' => 'Carbon Fiber', 'DailyRate' => 39.99, 'IsAvailable' => true,  'Terrain' => 'Downhill',      'WeightKg' => 15.3],
            ['BikeID' => 106, 'ModelName' => 'Mud Maverick',     'Brand' => 'TerraRide',  'GearCount' => 21, 'SuspensionType' => 'Full',     'FrameMaterial' => 'Aluminum',     'DailyRate' => 27.99, 'IsAvailable' => false, 'Terrain' => 'Enduro',        'WeightKg' => 13.1],
        ]));

        $registry->register('electric', new BikeService($electricRepo, 'bike_id', 'is_available', [
            ['bike_id' => 201, 'model_name' => 'Volt Rider',     'brand' => 'EcoMotion',   'battery_range_km' => 80,  'motor_power_w' => 500,  'daily_rate' => 29.99, 'is_available' => true,  'weight_kg' => 22.5, 'charge_time_h' => 4.5],
            ['bike_id' => 202, 'model_name' => 'City Glide',     'brand' => 'UrbanSpark',  'battery_range_km' => 60,  'motor_power_w' => 350,  'daily_rate' => 24.99, 'is_available' => true,  'weight_kg' => 19.8, 'charge_time_h' => 3.5],
            ['bike_id' => 203, 'model_name' => 'Trail Surge',    'brand' => 'EcoMotion',   'battery_range_km' => 100, 'motor_power_w' => 750,  'daily_rate' => 39.99, 'is_available' => false, 'weight_kg' => 25.0, 'charge_time_h' => 5.0],
            ['bike_id' => 204, 'model_name' => 'Commute Ease',   'brand' => 'CityWatt',    'battery_range_km' => 45,  'motor_power_w' => 250,  'daily_rate' => 19.99, 'is_available' => true,  'weight_kg' => 17.2, 'charge_time_h' => 3.0],
            ['bike_id' => 205, 'model_name' => 'Hill Climber X', 'brand' => 'UrbanSpark',  'battery_range_km' => 90,  'motor_power_w' => 1000, 'daily_rate' => 44.99, 'is_available' => true,  'weight_kg' => 27.3, 'charge_time_h' => 6.0],
            ['bike_id' => 206, 'model_name' => 'Breeze Electric', 'brand' => 'CityWatt',   'battery_range_km' => 70,  'motor_power_w' => 500,  'daily_rate' => 34.99, 'is_available' => false, 'weight_kg' => 21.0, 'charge_time_h' => 4.0],
        ]));

        self::$bikeRegistry = $registry;
        self::$accessoryService = new AccessoryService($accessoryRepo);
    }

    public static function getBikeRegistry(): ?BikeServiceRegistry
    {
        return self::$bikeRegistry;
    }

    public static function getBikeService(string $type): ?BikeServiceInterface
    {
        return self::$bikeRegistry?->get($type);
    }

    public static function getAccessoryService(): ?AccessoryService
    {
        return self::$accessoryService;
    }
}
