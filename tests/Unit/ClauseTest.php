<?php

use Laratribe\AdvancedFilters\Contracts\ClauseContract;
use Laratribe\AdvancedFilters\Filters\Clause;

it('exposes every case through the clause contract', function () {
    foreach (Clause::cases() as $case) {
        expect($case)->toBeInstanceOf(ClauseContract::class)
            ->and($case->operator())->toBe($case->value)
            ->and($case->label())->not->toBe('');
    }
});

it('gives every case a known value shape', function () {
    $shapes = [
        ClauseContract::SHAPE_NONE,
        ClauseContract::SHAPE_SINGLE,
        ClauseContract::SHAPE_RANGE,
        ClauseContract::SHAPE_MULTI,
    ];

    foreach (Clause::cases() as $case) {
        expect($case->valueShape())->toBeIn($shapes);
    }
});

it('shapes the operators the frontend branches on', function () {
    expect(Clause::IsEmpty->valueShape())->toBe(ClauseContract::SHAPE_NONE)
        ->and(Clause::IsNotEmpty->valueShape())->toBe(ClauseContract::SHAPE_NONE)
        ->and(Clause::Between->valueShape())->toBe(ClauseContract::SHAPE_RANGE)
        ->and(Clause::In->valueShape())->toBe(ClauseContract::SHAPE_MULTI)
        ->and(Clause::NotIn->valueShape())->toBe(ClauseContract::SHAPE_MULTI)
        ->and(Clause::Contains->valueShape())->toBe(ClauseContract::SHAPE_SINGLE);
});

/**
 * The JS keeps a fallback copy of these labels for field definitions that predate
 * `clauseItems`. Clause::label() is the source of truth — this catches the two
 * drifting apart.
 */
it('stays in sync with the fallback labels shipped in the JS', function () {
    $js = file_get_contents(__DIR__.'/../../resources/js/advanced-filters.js');

    expect($js)->not->toBeFalse();

    $found = preg_match('/export const OPERATOR_LABELS = \{(.*?)\n\}/s', (string) $js, $matches);
    expect($found)->toBe(1, 'Could not find OPERATOR_LABELS in advanced-filters.js');

    preg_match_all("/^\s*(\w+): '(.*?)',$/m", $matches[1], $pairs, PREG_SET_ORDER);

    $fromJs = [];
    foreach ($pairs as $pair) {
        $fromJs[$pair[1]] = $pair[2];
    }

    $fromPhp = [];
    foreach (Clause::cases() as $case) {
        $fromPhp[$case->value] = $case->label();
    }

    expect($fromJs)->toBe($fromPhp);
});
