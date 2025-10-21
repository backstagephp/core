<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>{!! trim($pageTitle ?? $content->pageTitle) !!}</title>
    {{ $headFirst ?? '' }}
    <meta charset="utf-8">
    @if($content)
    <link rel="canonical" href="{{ $content->url }}">
    @endif
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="apple-mobile-web-app-title" content="Backstage">
    <meta name="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="Backstage">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta property="author" content="Mark van Eijk">
    @if (isset($content->meta_tags['description']))
        <meta name="description" content="{{ $content->meta_tags['description'] }}">
        <meta property="og:description" content="{{ $content->meta_tags['description'] }}">
    @endif
    @if (count($content->meta_tags['keywords'] ?? []))
        <meta name="keywords" content="{{ implode(', ', $content->meta_tags['keywords']) }}">
    @endif
    {{ $headLast ?? '' }}
</head>

<body>
    {{ $slot }}
</body>

</html>
