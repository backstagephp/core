<?php

namespace Tests\Feature;

use Backstage\Fields\Models\Field;
use Backstage\Fields\Models\Type;
use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Language;
use Backstage\Models\Site;
use Backstage\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentFieldValueRepeaterTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_field_in_repeater_returns_array_not_string(): void
    {
        // Create test data
        $site = Site::factory()->create();
        $language = Language::factory()->create();
        $user = User::factory()->create();

        $type = Type::factory()->create([
            'site_id' => $site->id,
            'slug' => 'test-type',
        ]);

        // Create a repeater field
        $repeaterField = Field::factory()->create([
            'model_type' => 'type',
            'model_key' => 'ulid',
            'model_id' => $type->ulid,
            'field_type' => 'repeater',
            'slug' => 'test_repeater',
            'name' => 'Test Repeater',
            'config' => [
                'form' => [],
            ],
        ]);

        // Create a file field as child of repeater
        $fileField = Field::factory()->create([
            'model_type' => 'field',
            'model_key' => 'ulid',
            'model_id' => $repeaterField->ulid,
            'field_type' => 'uploadcare',
            'slug' => 'test_file',
            'name' => 'Test File',
            'config' => [
                'withMetadata' => false,
            ],
        ]);

        // Create content
        $content = Content::factory()->create([
            'type_slug' => $type->slug,
            'site_id' => $site->id,
            'language_code' => $language->code,
            'creator_id' => $user->id,
        ]);

        // Create a repeater value with file field data
        $repeaterValue = [
            [
                'test_file' => ['file1.jpg', 'file2.jpg'], // This should be an array
            ],
            [
                'test_file' => ['file3.jpg'], // This should also be an array
            ],
        ];

        ContentFieldValue::create([
            'content_ulid' => $content->ulid,
            'field_ulid' => $repeaterField->ulid,
            'value' => json_encode($repeaterValue),
        ]);

        // Test the getFormattedFieldValues method
        $formattedValues = $content->getFormattedFieldValues();

        // The repeater value should be decoded
        $this->assertIsArray($formattedValues[$repeaterField->ulid]);

        // The file field values inside the repeater should be arrays, not strings
        $repeaterData = $formattedValues[$repeaterField->ulid];
        $this->assertIsArray($repeaterData[0]['test_file']);
        $this->assertIsArray($repeaterData[1]['test_file']);

        // Check specific values
        $this->assertEquals(['file1.jpg', 'file2.jpg'], $repeaterData[0]['test_file']);
        $this->assertEquals(['file3.jpg'], $repeaterData[1]['test_file']);
    }

    public function test_file_field_in_repeater_with_double_encoded_json(): void
    {
        // Create test data
        $site = Site::factory()->create();
        $language = Language::factory()->create();
        $user = User::factory()->create();

        $type = Type::factory()->create([
            'site_id' => $site->id,
            'slug' => 'test-type-2',
        ]);

        // Create a repeater field
        $repeaterField = Field::factory()->create([
            'model_type' => 'type',
            'model_key' => 'ulid',
            'model_id' => $type->ulid,
            'field_type' => 'repeater',
            'slug' => 'test_repeater_2',
            'name' => 'Test Repeater 2',
            'config' => [
                'form' => [],
            ],
        ]);

        // Create a file field as child of repeater
        $fileField = Field::factory()->create([
            'model_type' => 'field',
            'model_key' => 'ulid',
            'model_id' => $repeaterField->ulid,
            'field_type' => 'uploadcare',
            'slug' => 'test_file_2',
            'name' => 'Test File 2',
            'config' => [
                'withMetadata' => false,
            ],
        ]);

        // Create content
        $content = Content::factory()->create([
            'type_slug' => $type->slug,
            'site_id' => $site->id,
            'language_code' => $language->code,
            'creator_id' => $user->id,
        ]);

        // Create a repeater value with double-encoded JSON (simulating the bug)
        $repeaterValue = [
            [
                'test_file_2' => json_encode(['file1.jpg', 'file2.jpg']), // Double encoded
            ],
            [
                'test_file_2' => json_encode(['file3.jpg']), // Double encoded
            ],
        ];

        ContentFieldValue::create([
            'content_ulid' => $content->ulid,
            'field_ulid' => $repeaterField->ulid,
            'value' => json_encode($repeaterValue),
        ]);

        // Test the getFormattedFieldValues method
        $formattedValues = $content->getFormattedFieldValues();

        // The repeater value should be decoded
        $this->assertIsArray($formattedValues[$repeaterField->ulid]);

        // The file field values inside the repeater should be arrays, not strings
        $repeaterData = $formattedValues[$repeaterField->ulid];
        $this->assertIsArray($repeaterData[0]['test_file_2']);
        $this->assertIsArray($repeaterData[1]['test_file_2']);

        // Check specific values
        $this->assertEquals(['file1.jpg', 'file2.jpg'], $repeaterData[0]['test_file_2']);
        $this->assertEquals(['file3.jpg'], $repeaterData[1]['test_file_2']);
    }
}
