<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>{!! html_entity_decode($title) !!}</title>
    <meta charset="utf-8">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="apple-mobile-web-app-title" content="Rocketeers">
    <meta name="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="Rocketeers">
    <meta name="robots" content="index,follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta property="author" content="Mark van Eijk">
    @if(filled($description))
    <meta name="description" content="{{ $description }}">
    <meta property="og:description" content="{{ $description }}">
    @endif
    @if(filled($keywords))
    <meta name="keywords" content="{{ $keywords }}">
    @endif
</head>
<body>
    {{ $slot }}
</body>
</html>