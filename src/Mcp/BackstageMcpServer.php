<?php

namespace Backstage\Mcp;

use Backstage\Models\Content;
use Backstage\Models\Type;
use Backstage\Models\Site;

class BackstageMcpServer
{
    /**
     * Get all available MCP tools for Backstage
     */
    public function getTools(): array
    {
        return [
            'backstage-search-content' => [
                'name' => 'backstage_search_content',
                'description' => 'Search content items for documentation and knowledge retrieval',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Search query to find relevant content'
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Limit number of results (default: 10)'
                        ]
                    ],
                    'required' => ['query']
                ]
            ],
            'backstage-get-content-details' => [
                'name' => 'backstage_get_content_details',
                'description' => 'Get detailed information about a specific content item',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'ulid' => [
                            'type' => 'string',
                            'description' => 'Content ULID'
                        ],
                        'slug' => [
                            'type' => 'string',
                            'description' => 'Content slug'
                        ]
                    ],
                    'oneOf' => [
                        ['required' => ['ulid']],
                        ['required' => ['slug']]
                    ]
                ]
            ],
            'backstage-get-content-types' => [
                'name' => 'backstage_get_content_types',
                'description' => 'Get all content types for documentation',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ]
        ];
    }

    /**
     * Handle MCP tool calls
     */
    public function handleToolCall(string $toolName, array $arguments): array
    {
        return match ($toolName) {
            'backstage_search_content' => $this->searchContent($arguments),
            'backstage_get_content_details' => $this->getContentDetails($arguments),
            'backstage_get_content_types' => $this->getContentTypes($arguments),
            default => [
                'error' => "Unknown tool: {$toolName}"
            ]
        };
    }

    private function searchContent(array $arguments): array
    {
        $query = Content::with(['type', 'site']);

        if (isset($arguments['query'])) {
            $searchTerm = $arguments['query'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('slug', 'LIKE', "%{$searchTerm}%");
            });
        }

        $limit = $arguments['limit'] ?? 10;
        $content = $query->limit($limit)->get();

        return [
            'results' => $content->map(function ($item) {
                return [
                    'ulid' => $item->ulid,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'type' => $item->type?->name,
                    'site' => $item->site?->name,
                    'public' => $item->public,
                    'summary' => $this->generateContentSummary($item),
                ];
            })->toArray(),
            'total' => $content->count(),
            'query' => $arguments['query'] ?? null
        ];
    }

    private function getContentDetails(array $arguments): array
    {
        $query = Content::with(['type', 'site']);

        if (isset($arguments['ulid'])) {
            $content = $query->where('ulid', $arguments['ulid'])->first();
        } else {
            $content = $query->where('slug', $arguments['slug'])->first();
        }

        if (!$content) {
            return ['error' => 'Content not found'];
        }

        return [
            'content' => [
                'ulid' => $content->ulid,
                'name' => $content->name,
                'slug' => $content->slug,
                'public' => $content->public,
                'type' => $content->type?->name,
                'site' => $content->site?->name,
                'created_at' => $content->created_at?->toISOString(),
                'updated_at' => $content->updated_at?->toISOString(),
                'summary' => $this->generateContentSummary($content),
            ]
        ];
    }

    private function getContentTypes(array $arguments): array
    {
        $types = Type::all();

        return [
            'content_types' => $types->map(function ($type) {
                return [
                    'slug' => $type->slug,
                    'name' => $type->name,
                    'name_plural' => $type->name_plural,
                    'public' => $type->public,
                ];
            })->toArray()
        ];
    }

    private function generateContentSummary(Content $content): string
    {
        $summary = "Content: {$content->name}";
        if ($content->type) {
            $summary .= " (Type: {$content->type->name})";
        }
        if ($content->site) {
            $summary .= " on {$content->site->name}";
        }
        $summary .= " - " . ($content->public ? 'Published' : 'Draft');
        return $summary;
    }
}