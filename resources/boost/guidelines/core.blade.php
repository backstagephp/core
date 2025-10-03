## Backstage CMS

Backstage is a Laravel-based CMS built with Filament that provides content management, forms, blocks, and multi-site capabilities.

### Key Features

- **Content Management**: Create and manage content with custom types and fields
- **Multi-site Support**: Manage multiple sites with different configurations
- **Form Builder**: Create and manage forms with custom fields
- **Block System**: Reusable content blocks for flexible page building
- **Menu Management**: Hierarchical navigation menus

### Content Structure

Backstage uses a flexible content system:

- **Content Types**: Define structure and fields for different content types
- **ULID-based IDs**: All content uses ULIDs instead of auto-incrementing IDs
- **Custom Fields**: Dynamic field system with various field types

@verbatim
<code-snippet name="Content Model Structure" lang="php">
// Content uses ULID as primary key
$content = Content::where('ulid', $ulid)->first();

// Content relationships
$content->type; // Content type
$content->site; // Associated site
$content->creator; // User who created it
</code-snippet>
@endverbatim

### Database Schema

Key tables and their relationships:

- **content**: Main content table (ULID primary key)
- **types**: Content types (slug primary key)
- **sites**: Multi-site configuration (ULID primary key)
- **forms**: Form definitions (slug primary key)
- **blocks**: Reusable content blocks (slug primary key)
- **menus**: Navigation menus (slug primary key)

### Best Practices

1. **Use ULIDs for content and sites**: Always use ULID-based lookups
2. **Use slugs for types, forms, blocks, menus**: These use slug-based identifiers
3. **Leverage relationships**: Use Eloquent relationships for efficient data loading
4. **Respect multi-tenancy**: Always consider site context when working with content

### Common Patterns

@verbatim
<code-snippet name="Content Query Pattern" lang="php">
// Query content with relationships
$content = Content::with(['type', 'site', 'creator'])
    ->where('site_ulid', $site->ulid)
    ->where('public', true)
    ->get();
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Type-based Content" lang="php">
// Get content by type
$pages = Content::whereHas('type', function ($q) {
    $q->where('slug', 'page');
})->get();
</code-snippet>
@endverbatim