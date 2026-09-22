<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Laratribe\AdvancedFilters\Facades\AdvancedFilters;
use Laratribe\AdvancedFilters\Tests\Fixtures\FilterTestModel;

function renderPanel(array $extra = []): string
{
    return Blade::render(
        '<x-advanced-filters::panel :fields="$fields" :active="$active" :base-url="$baseUrl" />',
        array_merge([
            'fields' => FilterTestModel::filterFieldsForFrontend(),
            'active' => [],
            'baseUrl' => 'https://example.test/records',
        ], $extra)
    );
}

it('renders the panel root with the Alpine controller bootstrapped', function () {
    $html = renderPanel();

    expect($html)
        ->toContain('data-af-part="panel"')
        ->toContain('advancedFiltersPanel(')
        ->toContain('data-af-part="builder"');
});

// @js() emits JSON.parse('...') with unicode-escaped quotes (\uXXXX); decode to read the JSON.
function decodeJsAttr(string $html): string
{
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', fn ($m) => mb_chr(hexdec($m[1])), $html);
}

it('serialises the field definitions into the data attribute', function () {
    $json = decodeJsAttr(renderPanel());

    expect($json)->toContain('"key":"name"')        // field defs
        ->toContain('"baseUrl"')                     // config blob present
        ->toContain('example.test');                 // base url in config (slashes JSON-escaped)
});

it('echoes back active filters', function () {
    $json = decodeJsAttr(renderPanel([
        'active' => [['field' => 'name', 'operator' => 'contains', 'value' => 'foo']],
    ]));

    expect($json)->toContain('"operator":"contains"');
});

it('renders every registered value input partial', function () {
    $html = renderPanel();

    expect($html)
        ->toContain("selectedField().input === 'select'")
        ->toContain("selectedField().input === 'number'")
        ->toContain("selectedField().input === 'date'")
        ->toContain("selectedField().input === 'string'");
});

// The point of the type registry: a new filter type reaches the panel without the
// panel being published or forked.
it('renders a custom input partial registered at runtime', function () {
    View::addNamespace('af-test', __DIR__.'/../Fixtures/views');
    AdvancedFilters::register('boolean', 'af-test::boolean');

    expect(renderPanel())->toContain("selectedField().input === 'boolean'");
});

it('restricts the rendered inputs when asked', function () {
    $html = Blade::render(
        '<x-advanced-filters::panel :fields="$fields" :inputs="[\'string\']" />',
        ['fields' => FilterTestModel::filterFieldsForFrontend()]
    );

    expect($html)->toContain("selectedField().input === 'string'")
        ->not->toContain("selectedField().input === 'number'");
});

it('serialises operator labels and value shapes for the frontend', function () {
    $json = decodeJsAttr(renderPanel());

    expect($json)->toContain('"clauseItems"')
        ->toContain('"label":"Contains"')
        ->toContain('"shape":"multi"');
});

it('renders panel markup supplied by the host app', function () {
    View::addNamespace('af-test', __DIR__.'/../Fixtures/views');

    $html = Blade::render(
        '<x-advanced-filters::panel :fields="$fields" view="af-test::panel" />',
        ['fields' => FilterTestModel::filterFieldsForFrontend()]
    );

    expect($html)->toContain('my-own-panel')
        ->not->toContain('data-af-part="builder"');
});

it('merges custom classes onto the root', function () {
    $html = Blade::render(
        '<x-advanced-filters::panel :fields="$fields" class="my-custom" />',
        ['fields' => []]
    );

    expect($html)->toContain('my-custom');
});
