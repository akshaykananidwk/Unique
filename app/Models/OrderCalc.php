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
 * Three calculation modes, taken from the line's category but settable per line:
 *   simple : amount = qty x rate
 *   sqft   : total sq.ft = qty x width x height (feet),  amount = total sq.ft x rate
 *   inch   : the same, with the width and height given in INCHES
 *
 * 'inch' is a way of typing a size, not a different way of charging for it. The customer
 * who says "eighteen by twenty-four" is describing 1.5ft x 2ft, and the shop's rates are
 * all per square foot, so an inch line is converted and billed by the square foot like any
 * other. What was typed is kept as typed, so the card can show the inches back.
 */
class OrderCalc
{
    public const MODES = ['simple', 'sqft', 'inch'];

    /** The modes that ask for a width and a height. */
    public const SIZED_MODES = ['sqft', 'inch'];

    public const INCHES_PER_FOOT = 12.0;

    /** Does this mode measure a size, in whichever unit? */
    public static function isSized(?string $mode): bool
    {
        return in_array((string)$mode, self::SIZED_MODES, true);
    }

    /** What the width and height of such a line are typed in: 'ft' or 'in'. */
    public static function unitOf(?string $mode): string
    {
        return (string)$mode === 'inch' ? 'in' : 'ft';
    }

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

        if (self::isSized($mode)) {
            // Quantity -> Width -> Height -> Total Sq.Ft. -> Rate -> Amount.
            // Inches become feet first; everything downstream is square feet.
            $wFt = $mode === 'inch' ? $w / self::INCHES_PER_FOOT : $w;
            $hFt = $mode === 'inch' ? $h / self::INCHES_PER_FOOT : $h;
            $totalSqft = self::money($qty * $wFt * $hFt);
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
            // Stored as typed — feet for 'sqft', inches for 'inch'. The mode says which.
            'width_ft'    => self::isSized($mode) ? $w : null,
            'height_ft'   => self::isSized($mode) ? $h : null,
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

    /**
     * Human-readable size, in the unit it was given in:
     *   "2 x 5ft x 2ft = 20 sq.ft"   ·   "2 x 18in x 24in = 6 sq.ft"
     */
    public static function sizeText(array $calc): string
    {
        $mode = (string)($calc['calc_mode'] ?? '');
        if (!self::isSized($mode) || !$calc['total_sqft']) {
            return '';
        }
        $u = self::unitOf($mode);
        $n = static fn($v) => rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.');
        return $n($calc['qty']) . ' x ' . $n($calc['width_ft']) . $u . ' x ' . $n($calc['height_ft']) . $u
            . ' = ' . $n($calc['total_sqft']) . ' sq.ft';
    }
}
