<?php

use Backstage\Fields\Models\Field;
use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Tests\TestCase;

uses(TestCase::class);

test('it hydrates relations in repeater fields', function () {

    $relatedContent1 = Content::factory()->create(['name' => 'Related 1']);
    $relatedContent2 = Content::factory()->create(['name' => 'Related 2']);

    $repeaterField = Field::factory()->create([
        'field_type' => 'repeater',
        'slug' => 'my_repeater',
        'name' => 'My Repeater',
        'model_type' => 'content',
        'model_key' => 'dummy',
        'config' => [
            'columns' => 1,
        ],
    ]);

    $selectField = Field::factory()->create([
        'parent_ulid' => $repeaterField->ulid,
        'field_type' => 'select',
        'slug' => 'my_select',
        'name' => 'My Select',
        'model_type' => 'content',
        'model_key' => 'dummy',
        'config' => [
            'relations' => [
                [
                    'resource' => Content::class,
                    'relationKey' => 'ulid',
                    'relationValue' => 'name',
                ],
            ],
            'optionType' => ['relationship'],
        ],
    ]);

    $content = Content::factory()->create();

    $repeaterValue = [
        [
            'my_select' => $relatedContent1->ulid,
        ],
        [
            'my_select' => $relatedContent2->ulid,
        ],
    ];

    $fieldValue = ContentFieldValue::create([
        'content_ulid' => $content->ulid,
        'field_ulid' => $repeaterField->ulid,
        'value' => json_encode($repeaterValue),
    ]);

    $hydratedValue = $fieldValue->value();

    expect($hydratedValue)->toBeArray()
        ->and($hydratedValue)->toHaveCount(2);

    expect($hydratedValue[0]['my_select'])->toBeInstanceOf(Content::class)
        ->and($hydratedValue[0]['my_select']->ulid)->toBe($relatedContent1->ulid);

    expect($hydratedValue[1]['my_select'])->toBeInstanceOf(Content::class)
        ->and($hydratedValue[1]['my_select']->ulid)->toBe($relatedContent2->ulid);
});
