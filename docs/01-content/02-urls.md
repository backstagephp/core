---
title: URLs
---

# URLs

## URL Structure

A URL is built from these parts, in order:

1. **Domain**: Main site domain.
2. **Site Path** (optional): Identifies a site instance (e.g., `/blog`).
3. **Language Path** (optional): Language segment (e.g., `/en`, `/nl`).
4. **Content Path**: Specific page or content (e.g., `/contact`).

Format:

```
http(s)://[www.]domain[/site-path][/language-path]/content-path
```

Notes:
- SSL can be enforced (redirect to `https://`).
- Optionally choose `www` or non-`www`.
- Each domain can have multiple languages, each with an optional path.
- Content path is defined in `content`.

## Examples

- `https://example.com/nl/contact`
    - Domain: `example.com`
    - Language path: `/nl`
    - Content path: `/contact`

- `https://example.com/blog/de/contact`
    - Domain: `example.com`
    - Site path: `/blog`
    - Language path: `/de`
    - Content path: `/contact`

- `https://example.com/contact`
    - Domain: `example.com`
    - Content path: `/contact`

See `RequestServiceProvider.php` for implementation details.
