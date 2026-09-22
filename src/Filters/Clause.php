<?php

namespace Laratribe\AdvancedFilters\Filters;

use Laratribe\AdvancedFilters\Contracts\ClauseContract;

/**
 * The built-in operator set.
 *
 * These labels are the single source of truth for the operator names shown in the
 * UI — they reach the frontend through the `clauseItems` key of the wire contract.
 */
enum Clause: string implements ClauseContract
{
    case Contains = 'contains';
    case NotContains = 'not_contains';
    case StartsWith = 'starts_with';
    case EndsWith = 'ends_with';
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case GreaterThan = 'greater_than';
    case LessThan = 'less_than';
    case GreaterThanOrEqual = 'greater_than_or_equal';
    case LessThanOrEqual = 'less_than_or_equal';
    case Between = 'between';
    case In = 'in';
    case NotIn = 'not_in';
    case IsEmpty = 'is_empty';
    case IsNotEmpty = 'is_not_empty';

    public function operator(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Contains => 'Contains',
            self::NotContains => 'Does not contain',
            self::StartsWith => 'Starts with',
            self::EndsWith => 'Ends with',
            self::Equals => 'Equals',
            self::NotEquals => 'Not equals',
            self::GreaterThan => 'Greater than',
            self::LessThan => 'Less than',
            self::GreaterThanOrEqual => '≥',
            self::LessThanOrEqual => '≤',
            self::Between => 'Between',
            self::In => 'Is any of',
            self::NotIn => 'Is none of',
            self::IsEmpty => 'Is empty',
            self::IsNotEmpty => 'Is not empty',
        };
    }

    public function valueShape(): string
    {
        return match ($this) {
            self::IsEmpty, self::IsNotEmpty => self::SHAPE_NONE,
            self::Between => self::SHAPE_RANGE,
            self::In, self::NotIn => self::SHAPE_MULTI,
            default => self::SHAPE_SINGLE,
        };
    }
}
