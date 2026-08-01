<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

/** Server-side price calculation from item + options + slabs + price group. */
class Pricing
{
    /**
     * @param array $item items row
     * @param float $qty
     * @param array $spec field_key => value (option value ids for select/radio, raw for others)
     * @param float $groupDiscountPercent dealer price-group discount
     * @return array{rate:float,amount:float,spec_text:string,billed_qty:float}
     */
    public static function calculate(array $item, float $qty, array $spec, float $groupDiscountPercent = 0.0): array
    {
        $rate = (float)$item['base_price'];
        $options = DB::all(
            'SELECT * FROM `' . tbl('item_options') . '` WHERE item_id = ? ORDER BY sort_order, id',
            [(int)$item['id']]
        );

        // Slab pricing replaces the base rate by quantity
        if ($item['pricing_type'] === 'slab') {
            $slab = DB::get(
                'SELECT rate FROM `' . tbl('price_slabs') . '`
                 WHERE item_id = ? AND min_qty <= ? AND (max_qty IS NULL OR max_qty >= ?)
                 ORDER BY min_qty DESC LIMIT 1',
                [(int)$item['id'], $qty, $qty]
            );
            if ($slab) {
                $rate = (float)$slab['rate'];
            }
        }

        $specTextParts = [];
        $multipliers = [];
        $additions = 0.0;
        foreach ($options as $option) {
            $key = (string)$option['field_key'];
            $value = $spec[$key] ?? null;
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            if (in_array($option['field_type'], ['select', 'radio', 'checkbox'], true)) {
                $ids = is_array($value) ? $value : [$value];
                $labels = [];
                foreach ($ids as $valueId) {
                    $optValue = DB::get(
                        'SELECT * FROM `' . tbl('item_option_values') . '` WHERE id = ? AND option_id = ?',
                        [(int)$valueId, (int)$option['id']]
                    );
                    if (!$optValue) {
                        continue;
                    }
                    $labels[] = (string)$optValue['label'];
                    if ((int)$option['affects_price'] === 1) {
                        $delta = (float)$optValue['price_delta'];
                        switch ($optValue['price_mode']) {
                            case 'add':
                                $additions += $delta;
                                break;
                            case 'multiply':
                                $multipliers[] = $delta;
                                break;
                            case 'replace':
                                $rate = $delta;
                                break;
                        }
                    }
                }
                if ($labels) {
                    $specTextParts[] = $option['label'] . ': ' . implode(', ', $labels);
                }
            } elseif ($option['field_type'] !== 'file') {
                $flat = is_array($value) ? implode(', ', array_map('strval', $value)) : (string)$value;
                $specTextParts[] = $option['label'] . ': ' . mb_substr($flat, 0, 300);
            }
        }

        $rate += $additions;
        foreach ($multipliers as $m) {
            $rate *= $m;
        }
        if ($groupDiscountPercent > 0) {
            $rate = $rate * (1 - $groupDiscountPercent / 100);
        }
        $rate = round($rate, 2);

        // Billed quantity: area items bill width × height × qty
        $billedQty = $qty;
        if ($item['pricing_type'] === 'area') {
            $w = (float)($spec['width_ft'] ?? 0);
            $h = (float)($spec['height_ft'] ?? 0);
            if ($w > 0 && $h > 0) {
                $billedQty = round($w * $h * max(1, $qty), 2);
                $specTextParts[] = 'Size: ' . $w . 'ft × ' . $h . 'ft';
            }
        }

        $amount = $item['pricing_type'] === 'fixed'
            ? $rate
            : round($rate * $billedQty, 2);

        return [
            'rate' => $rate,
            'amount' => $amount,
            'spec_text' => implode(' | ', $specTextParts),
            'billed_qty' => $billedQty,
        ];
    }
}
