<?php
declare(strict_types=1);

namespace App\Models;

/**
 * The one and only place order money is worked out.
 *
 * New Order, Edit Order, the saved record and the printed bill all run through here, so the
 * three can never disagree. The browser mirrors these exact formulas for live feedback, but
 * whatever it shows is recomputed here on save — the server is always the authority.
 *
 * Two calculation modes, taken from the line's category:
 *   simple : amount = qty x rate
 *   sqft   : total sq.ft = qty x width x height, amount = total sq.ft x rate
 */
class OrderCalc
{
    public const MODES = ['simple', 'sqft'];

    /** Round money to paise; keeps every stage of the sum consistent. */
    public static function money(float $n): float
    {
        return round($n, 2);
    }

    /**
     * Work out one order line.
     *
     * @param array $in calc_mode, qty, width_ft, height_ft, rate, tax_percent
     * @return array{calc_mode:string,qty:float,width_ft:?float,height_ft:?float,total_sqft:?float,
     *               billed_qty:float,rate:float,amount:float,tax_percent:float,tax_amount:float,line_total:float}
     */
    public static function line(array $in): array
    {
        $mode = in_array($in['calc_mode'] ?? 'simple', self::MODES, true) ? $in['calc_mode'] : 'simple';

        $qty  = max(0.0, round((float)($in['qty'] ?? 0), 2));
        $rate = max(0.0, round((float)($in['rate'] ?? 0), 2));
        $w    = max(0.0, round((float)($in['width_ft'] ?? 0), 2));
        $h    = max(0.0, round((float)($in['height_ft'] ?? 0), 2));

        if ($mode === 'sqft') {
            // Quantity -> Width -> Height -> Total Sq.Ft. -> Rate -> Amount
            $totalSqft = self::money($qty * $w * $h);
            $billed    = $totalSqft;
        } else {
            $totalSqft = null;
            $billed    = $qty;
        }

        $amount     = self::money($billed * $rate);
        $taxPercent = max(0.0, (float)($in['tax_percent'] ?? 0));
        $taxAmount  = self::money($amount * $taxPercent / 100);

        return [
            'calc_mode'   => $mode,
            'qty'         => $qty,
            'width_ft'    => $mode === 'sqft' ? $w : null,
            'height_ft'   => $mode === 'sqft' ? $h : null,
            'total_sqft'  => $totalSqft,
            'billed_qty'  => $billed,
            'rate'        => $rate,
            'amount'      => $amount,
            'tax_percent' => $taxPercent,
            'tax_amount'  => $taxAmount,
            'line_total'  => self::money($amount + $taxAmount),
        ];
    }

    /**
     * Roll a set of already-calculated lines into the order totals.
     * No discount — this shop does not give one.
     *
     * @param array $lines each with amount + tax_amount (cancelled lines must be filtered out first)
     * @return array{subtotal:float,tax_amount:float,delivery_charge:float,round_off:float,total:float}
     */
    public static function totals(array $lines, float $deliveryCharge = 0.0): array
    {
        $subtotal = 0.0;
        $tax = 0.0;
        foreach ($lines as $line) {
            $subtotal += (float)($line['amount'] ?? 0);
            $tax      += (float)($line['tax_amount'] ?? 0);
        }
        $subtotal = self::money($subtotal);
        $tax      = self::money($tax);
        $delivery = self::money(max(0.0, $deliveryCharge));

        $raw   = $subtotal + $tax + $delivery;
        $total = round($raw);                    // bill to the nearest rupee
        return [
            'subtotal'        => $subtotal,
            'tax_amount'      => $tax,
            'delivery_charge' => $delivery,
            'round_off'       => self::money($total - $raw),
            'total'           => (float)$total,
        ];
    }

    /** Human-readable size for a sq.ft line, e.g. "2 x 5ft x 2ft = 20 sq.ft". */
    public static function sizeText(array $calc): string
    {
        if (($calc['calc_mode'] ?? '') !== 'sqft' || !$calc['total_sqft']) {
            return '';
        }
        $n = static fn($v) => rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
        return $n($calc['qty']) . ' x ' . $n($calc['width_ft']) . 'ft x ' . $n($calc['height_ft']) . 'ft = '
            . $n($calc['total_sqft']) . ' sq.ft';
    }
}
