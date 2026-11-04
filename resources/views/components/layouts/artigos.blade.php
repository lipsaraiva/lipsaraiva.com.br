<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }}</title>
<meta name="description" content="{{ $desc }}">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:type" content="{{ $type ?? 'website' }}">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="SARAIVA">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ url()->current() }}">
@isset($image)
<meta property="og:image" content="{{ $image }}">
<meta name="twitter:card" content="summary_large_image">
@endisset
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/artigos.css">
@stack('head')
</head>
<body>
<header class="top">
  <a href="/" class="logo">SARAIVA</a>
  <nav><a href="{{ route('artigos.index') }}">Artigos</a><a href="/#contato">Contato</a></nav>
</header>
{{ $slot }}
<footer class="foot">
  <span>© {{ date('Y') }} Saraiva · Todos os direitos reservados</span>
  <a href="/">lipsaraiva.com.br</a>
</footer>
</body>
</html>
