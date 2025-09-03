<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run if the tables exist
        if (! Schema::hasTable('content_field_values') && ! Schema::hasTable('settings')) {
            return;
        }

        // Get all RichEditor field ULIDs
        $richEditorFields = DB::table('fields')
            ->where('field_type', 'rich-editor')
            ->pluck('ulid')
            ->toArray();

        if (empty($richEditorFields)) {
            return;
        }

        // Convert all content field values that are RichEditor fields from HTML strings to Filament v4 array format
        if (Schema::hasTable('content_field_values')) {
            DB::table('content_field_values')
                ->whereIn('field_ulid', $richEditorFields)
                ->whereNotNull('value')
                ->orderBy('ulid')
                ->chunk(100, function ($rows) {
                    foreach ($rows as $row) {
                        $value = $row->value;

                        // Skip if already in array format (starts with { or [)
                        if (is_string($value) && (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))) {
                            continue;
                        }

                        // Skip if empty or null
                        if (empty($value)) {
                            continue;
                        }

                        // Convert HTML string to Filament v4 array format
                        $convertedValue = $this->convertHtmlToRichEditorArray($value);

                        // Update the database
                        DB::table('content_field_values')
                            ->where('ulid', $row->ulid)
                            ->update(['value' => json_encode($convertedValue)]);

                        logger("Converted RichEditor content field value: {$row->ulid}");
                    }
                });
        }

        // Also convert settings that contain RichEditor fields
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->whereNotNull('values')
                ->orderBy('ulid')
                ->chunk(100, function ($rows) use ($richEditorFields) {
                    foreach ($rows as $row) {
                        $values = $row->values;

                        if (empty($values)) {
                            continue;
                        }

                        $decodedValues = json_decode($values, true);
                        if (! is_array($decodedValues)) {
                            continue;
                        }

                        $updated = false;
                        $newValues = $this->convertRichEditorInSettings($decodedValues, $richEditorFields, $updated);

                        if ($updated) {
                            DB::table('settings')
                                ->where('ulid', $row->ulid)
                                ->update(['values' => json_encode($newValues)]);

                            logger("Converted RichEditor settings value: {$row->ulid}");
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all RichEditor field ULIDs
        $richEditorFields = DB::table('fields')
            ->where('field_type', 'rich-editor')
            ->pluck('ulid')
            ->toArray();

        if (empty($richEditorFields)) {
            return;
        }

        // Convert content field values back from Filament v4 array format to HTML strings
        if (Schema::hasTable('content_field_values')) {
            DB::table('content_field_values')
                ->whereIn('field_ulid', $richEditorFields)
                ->whereNotNull('value')
                ->orderBy('ulid')
                ->chunk(100, function ($rows) {
                    foreach ($rows as $row) {
                        $value = $row->value;

                        // Skip if not in array format
                        if (! is_string($value) || (! str_starts_with(trim($value), '{') && ! str_starts_with(trim($value), '['))) {
                            continue;
                        }

                        $decodedValue = json_decode($value, true);
                        if (! is_array($decodedValue)) {
                            continue;
                        }

                        // Convert Filament v4 array format back to HTML string
                        $convertedValue = $this->convertRichEditorArrayToHtml($decodedValue);

                        // Update the database
                        DB::table('content_field_values')
                            ->where('ulid', $row->ulid)
                            ->update(['value' => $convertedValue]);

                        logger("Reverted RichEditor content field value: {$row->ulid}");
                    }
                });
        }

        // Also convert settings back
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->whereNotNull('values')
                ->orderBy('ulid')
                ->chunk(100, function ($rows) use ($richEditorFields) {
                    foreach ($rows as $row) {
                        $values = $row->values;

                        if (empty($values)) {
                            continue;
                        }

                        $decodedValues = json_decode($values, true);
                        if (! is_array($decodedValues)) {
                            continue;
                        }

                        $updated = false;
                        $newValues = $this->revertRichEditorInSettings($decodedValues, $richEditorFields, $updated);

                        if ($updated) {
                            DB::table('settings')
                                ->where('ulid', $row->ulid)
                                ->update(['values' => json_encode($newValues)]);

                            logger("Reverted RichEditor settings value: {$row->ulid}");
                        }
                    }
                });
        }
    }

    /**
     * Convert RichEditor fields in settings data
     */
    private function convertRichEditorInSettings($data, array $richEditorFields, &$updated)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value) && in_array($key, $richEditorFields)) {
                    $data[$key] = $this->convertHtmlToRichEditorArray($value);
                    $updated = true;
                } elseif (is_array($value)) {
                    $data[$key] = $this->convertRichEditorInSettings($value, $richEditorFields, $updated);
                }
            }
        }

        return $data;
    }

    /**
     * Revert RichEditor fields in settings data
     */
    private function revertRichEditorInSettings($data, array $richEditorFields, &$updated)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) && in_array($key, $richEditorFields)) {
                    $data[$key] = $this->convertRichEditorArrayToHtml($value);
                    $updated = true;
                } elseif (is_array($value)) {
                    $data[$key] = $this->revertRichEditorInSettings($value, $richEditorFields, $updated);
                }
            }
        }

        return $data;
    }

    /**
     * Convert HTML string to Filament v4 RichEditor array format
     */
    private function convertHtmlToRichEditorArray(string $html): array
    {
        if (empty($html)) {
            return [
                'type' => 'doc',
                'content' => [],
            ];
        }

        // For now, create a simple paragraph structure
        // In a more sophisticated implementation, you'd parse the HTML properly
        $text = strip_tags($html);

        if (empty($text)) {
            return [
                'type' => 'doc',
                'content' => [],
            ];
        }

        return [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $text,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Convert Filament v4 RichEditor array format back to HTML string
     */
    private function convertRichEditorArrayToHtml(array $data): string
    {
        if (! isset($data['type']) || $data['type'] !== 'doc') {
            return '';
        }

        return $this->convertNodeToHtml($data);
    }

    /**
     * Convert a node to HTML recursively
     */
    private function convertNodeToHtml(array $node): string
    {
        $html = '';

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $html .= $this->convertNodeToHtml($child);
            }
        }

        switch ($node['type'] ?? '') {
            case 'paragraph':
                $align = $node['attrs']['textAlign'] ?? 'start';
                $alignClass = $align !== 'start' ? " style=\"text-align: {$align}\"" : '';

                return "<p{$alignClass}>{$html}</p>";

            case 'text':
                $text = $node['text'] ?? '';
                $marks = $node['marks'] ?? [];

                foreach ($marks as $mark) {
                    switch ($mark['type'] ?? '') {
                        case 'bold':
                            $text = "<strong>{$text}</strong>";

                            break;
                        case 'italic':
                            $text = "<em>{$text}</em>";

                            break;
                        case 'underline':
                            $text = "<u>{$text}</u>";

                            break;
                        case 'strike':
                            $text = "<s>{$text}</s>";

                            break;
                        case 'code':
                            $text = "<code>{$text}</code>";

                            break;
                        case 'link':
                            $href = $mark['attrs']['href'] ?? '#';
                            $text = "<a href=\"{$href}\">{$text}</a>";

                            break;
                    }
                }

                return $text;

            case 'heading':
                $level = $node['attrs']['level'] ?? 1;

                return "<h{$level}>{$html}</h{$level}>";

            case 'bulletList':
                return "<ul>{$html}</ul>";

            case 'orderedList':
                return "<ol>{$html}</ol>";

            case 'listItem':
                return "<li>{$html}</li>";

            case 'blockquote':
                return "<blockquote>{$html}</blockquote>";

            case 'codeBlock':
                return "<pre><code>{$html}</code></pre>";

            case 'hardBreak':
                return '<br>';

            case 'horizontalRule':
                return '<hr>';

            default:
                return $html;
        }
    }
};
