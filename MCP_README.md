# Backstage MCP Server

A simple MCP server for Backstage CMS that provides knowledge retrieval tools for AI assistants.

## Features

- **Content Search**: Search content by name or slug
- **Content Details**: Get detailed information about specific content
- **Content Types**: List all available content types

## Usage

```bash
# List available tools
php artisan backstage:mcp tools

# Test content search
php artisan backstage:mcp test --tool=backstage_search_content --args='{"query":"payment"}'

# Test content details
php artisan backstage:mcp test --tool=backstage_get_content_details --args='{"ulid":"01k4qgv3m5qk1sn23svw1jmv0z"}'

# Test content types
php artisan backstage:mcp test --tool=backstage_get_content_types --args='{}'
```

## Integration with Laravel Boost

The MCP server integrates with Laravel Boost through AI guidelines. When users install your package and run `php artisan boost:install`, the Backstage guidelines will be automatically available.

## Available Tools

- `backstage_search_content` - Search content items
- `backstage_get_content_details` - Get content details
- `backstage_get_content_types` - List content types