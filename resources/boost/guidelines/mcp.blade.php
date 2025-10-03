## Backstage MCP Server

The Backstage MCP server provides AI-powered tools for documentation and knowledge retrieval from your Backstage CMS.

### Available MCP Tools

- **backstage_search_content**: Search content by name, slug, or type
- **backstage_get_content_details**: Get detailed content information
- **backstage_get_content_types**: List all content types with their structure

### Usage Examples

@verbatim
<code-snippet name="Search Content" lang="bash">
# Search for content about "payment"
php artisan backstage:mcp test --tool=backstage_search_content --args='{"query":"payment","limit":5}'
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Get Content Details" lang="bash">
# Get detailed information about specific content
php artisan backstage:mcp test --tool=backstage_get_content_details --args='{"ulid":"01k4qgv3m5qk1sn23svw1jmv0z"}'
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Get Content Types" lang="bash">
# Get all content types
php artisan backstage:mcp test --tool=backstage_get_content_types --args='{}'
</code-snippet>
@endverbatim

### Tool Response Format

All MCP tools return structured JSON responses with:

- **results/content**: The main data array
- **total**: Number of results found
- **query**: The search query used (for search tools)
- **summary**: Human-readable content summaries

### Integration with Laravel Boost

The Backstage MCP server integrates seamlessly with Laravel Boost:

1. **Automatic Discovery**: Boost automatically detects the MCP server
2. **AI Guidelines**: Provides context about Backstage CMS structure
3. **Code Generation**: AI can generate Backstage-specific code using the MCP tools

### Best Practices for AI Development

1. **Use Search First**: Always search for existing content before creating new items
2. **Leverage Structure Info**: Use content type information to understand the system
3. **Check Relationships**: Understand how content relates to sites and types
4. **Read Documentation**: Use the documentation features to understand Backstage concepts