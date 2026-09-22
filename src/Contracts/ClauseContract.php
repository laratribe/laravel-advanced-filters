<?php

namespace Laratribe\AdvancedFilters\Contracts;

use Laratribe\AdvancedFilters\Filters\Clause;
use Laratribe\AdvancedFilters\Filters\CustomClause;

/**
 * An operator a filter can be asked to apply.
 *
 * {@see Clause} is the built-in set. Implement this interface to add your own —
 * either as a backed enum, which keeps operators reusable and `match`-able:
 *
 *     enum DateClause: string implements ClauseContract
 *     {
 *         case WithinLastDays = 'within_last_days';
 *         public function operator(): string { return $this->value; }
 *         public function label(): string { return 'Within last N days'; }
 *         public function valueShape(): string { return self::SHAPE_SINGLE; }
 *     }
 *
 * — or ad hoc via {@see CustomClause::make()}, which can also carry the query and
 * validation closures inline.
 */
interface ClauseContract
{
    /** No value input at all (e.g. is_empty). */
    public const SHAPE_NONE = 'none';

    /** One value (the default). */
    public const SHAPE_SINGLE = 'single';

    /** Two values — `value` and `valueTo` (e.g. between). */
    public const SHAPE_RANGE = 'range';

    /** A list of values (e.g. in / not_in). */
    public const SHAPE_MULTI = 'multi';

    /**
     * The wire identifier — what the frontend sends as `operator`.
     *
     * Deliberately not named value(): a backed enum already exposes a readonly
     * `$value` property, so `$clause->value` would work on Clause and fatal on a
     * plain-class implementation.
     */
    public function operator(): string;

    /**
     * The human label shown in the operator dropdown and on chips.
     */
    public function label(): string;

    /**
     * How many values the operator takes — one of the SHAPE_* constants.
     * The frontend uses this to pick and validate the value input.
     */
    public function valueShape(): string;
}
