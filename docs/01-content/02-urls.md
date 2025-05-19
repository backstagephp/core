---
title: Urls
---

## Overview

Explanation of how urls are being generated.

The url is based on: content-path, site-path, domain, language-path.

This is the url that is being parsed:

```
http(s)?://(www.)?(domain)/(site_path/)?(language_code_path/)?(content_path)
```

1. Depending if SSL is forced, the user is redirected to https://.
2. Optional (a future setting) you can choise to either use the www or non-www version of the website.
3. The domain that is setup for the site.
4. The path that is setup for the site.
5. Each domain have one or more languages. Each language of the domain has an optional path.
6. The path setup in `content`.

### Examples

Url: `https://example.com/nl/contact` should belong to:

1. A site with example.com as domain.
2. A language for that domain with as path `nl` setup.
3. Content with the same language and as path `/contact`.

Url: `https://example.com/blog/de/contact` should belong to:

1. A site with example.com as domain;
2. The site with `/blog` as path.
3. The path language of the domain set to `/de`.
4. Content with the same language and as path `/contact`.

**Of, course. The most basic usage will work to:**

Url: `https://example.com/contact` should belong to:

1. A site with domain example.com.
2. A content belonging to that site with as path `/contact`.

For more information see `RequestServiceProvider.php`.