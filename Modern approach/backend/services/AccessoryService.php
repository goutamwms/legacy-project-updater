<?php

declare(strict_types=1);

/**
 * Business logic for accessory browsing and ordering.
 *
 * Handles compatibility filtering, order processing with stock
 * deduction, and a bundle discount (10 % off when buying
 * Water Bottle + Bike Light together).
 */
class AccessoryService
{
    private AccessoryRepository $repo;

    /** Accessory ID for the Water Bottle (bundle-part A). */
    public const int BUNDLE_ID_A = 1;
    /** Accessory ID for the Bike Light (bundle-part B). */
    public const int BUNDLE_ID_B = 3;
    /** Discount rate applied when both bundle items are ordered. */
    public const float BUNDLE_DISCOUNT_RATE = 0.10;

    public function __construct(AccessoryRepository $repo)
    {
        $this->repo = $repo;
    }

    /** @return list<array<string, mixed>> */
    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    /**
     * Filter accessories by bike-type compatibility.
     * Returns items whose CompatibleWith list contains the given type or "all".
     * Input is sanitised (lower-cased, stripped of HTML tags).
     * @return list<array<string, mixed>>
     */
    public function getCompatibleWith(string $bikeType): array
    {
        $bikeType = strip_tags($bikeType);
        $bikeType = strtolower(trim($bikeType));

        $accessories = $this->repo->getAll();
        /** @var list<array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>}> $accessories */

        $filtered = array_filter(
            $accessories,
            fn (array $accessory): bool =>
                in_array($bikeType, $accessory['CompatibleWith'], true)
                || in_array('all', $accessory['CompatibleWith'], true)
        );

        return array_values($filtered);
    }

    /**
     * Validate and process an accessory order.
     *
     * Checks stock availability, applies the bundle discount when
     * applicable, deducts quantities, and persists changes.
     *
     * @param list<array{AccessoryID: int, Quantity: int}> $quantities
     * @return array{Success: bool, Message: string, TotalPrice: float, DiscountAmount: float, BundleDiscountApplied: bool}
     */
    public function processOrder(array $quantities): array
    {
        $accessories = $this->repo->getAll();
        /** @var list<array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>}> $accessories */

        $accessoryMap = [];
        foreach ($accessories as &$acc) {
            $accessoryMap[$acc['AccessoryID']] = &$acc;
        }
        unset($acc);

        $orderedQuantities = [];
        foreach ($quantities as $item) {
            $id  = $item['AccessoryID'];
            $qty = $item['Quantity'];

            if ($qty <= 0) {
                continue;
            }

            if (!isset($accessoryMap[$id])) {
                return [
                    'Success'               => false,
                    'Message'               => 'Accessory ID ' . $id . ' not found.',
                    'TotalPrice'            => 0.0,
                    'DiscountAmount'        => 0.0,
                    'BundleDiscountApplied' => false,
                ];
            }

            if ($accessoryMap[$id]['StockCount'] < $qty) {
                return [
                    'Success'               => false,
                    'Message'               => 'Not enough stock for: ' . $accessoryMap[$id]['Name']
                                                . '. Available: ' . $accessoryMap[$id]['StockCount']
                                                . '. Requested: ' . $qty . '.',
                    'TotalPrice'            => 0.0,
                    'DiscountAmount'        => 0.0,
                    'BundleDiscountApplied' => false,
                ];
            }

            $orderedQuantities[$id] = $qty;
        }

        if (empty($orderedQuantities)) {
            return [
                'Success'               => false,
                'Message'               => 'No items ordered.',
                'TotalPrice'            => 0.0,
                'DiscountAmount'        => 0.0,
                'BundleDiscountApplied' => false,
            ];
        }

        $subtotal = 0.0;
        foreach ($orderedQuantities as $id => $qty) {
            $subtotal += $accessoryMap[$id]['UnitPrice'] * $qty;
        }

        $bundleApplied  = isset($orderedQuantities[self::BUNDLE_ID_A])
                       && isset($orderedQuantities[self::BUNDLE_ID_B]);
        $discountAmount = $bundleApplied ? round($subtotal * self::BUNDLE_DISCOUNT_RATE, 2) : 0.0;
        $totalPrice     = round($subtotal - $discountAmount, 2);

        foreach ($orderedQuantities as $id => $qty) {
            $accessoryMap[$id]['StockCount'] -= $qty;
        }

        $this->repo->save($accessories);

        return [
            'Success'               => true,
            'Message'               => $bundleApplied
                ? 'Order placed! Bundle deal applied: 10% off for Water Bottle + Bike Light.'
                : 'Order placed successfully.',
            'TotalPrice'            => $totalPrice,
            'DiscountAmount'        => $discountAmount,
            'BundleDiscountApplied' => $bundleApplied,
        ];
    }

    public function resetToDefaults(): void
    {
        $accessories = $this->repo->getAll();
        /** @var list<array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>}> $accessories */

        $defaults = [1 => 15, 2 => 8, 3 => 20, 4 => 6];

        foreach ($accessories as &$acc) {
            if (isset($defaults[$acc['AccessoryID']])) {
                $acc['StockCount'] = $defaults[$acc['AccessoryID']];
            }
        }
        unset($acc);

        $this->repo->save($accessories);
    }
}
