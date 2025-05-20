# Retrieving Data from Fields

## Relationship Fields

When working with relationship fields (`Select` or `CheckboxList`), you can easily access the related models through Laravel's relationship methods. These fields store relationships in the `relationables` table, allowing for flexible and powerful model associations.

### Basic Usage

```php
// Get a content model
$company = Content::where('type_slug', 'member')->first();

// Access related content through the relationship
$relatedContent = $content->content()->get();
```

### Filtering Related Content

You can filter the related content using standard Laravel query methods:

```php
// Get only related content of type 'case'
$cases = $company->content()
    ->where('type_slug', 'case')
    ->get();
```

### Relationship Structure

The relationships are stored in the `relationables` table with the following structure:
- `relation_type`: The type of the relation (e.g., 'content')
- `relation_ulid`: The ULID of the relation model
- `related_type`: The type of the related model
- `related_ulid`: The ULID of the related model

This structure allows for flexible polymorphic relationships between different types of content.

