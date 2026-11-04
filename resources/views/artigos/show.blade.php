<x-layouts.artigos :title="$post->title.' — SARAIVA'" :desc="$post->description" :image="$post->coverUrl()" type="article">
@push('head')
@if ($post->published_at)
<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
@endif
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org', '@type' => 'BlogPosting',
  'headline' => $post->title, 'description' => $post->description, 'image' => $post->coverUrl(),
  'datePublished' => $post->published_at?->toIso8601String(), 'dateModified' => $post->updated_at->toIso8601String(),
  'mainEntityOfPage' => route('artigos.show', $post),
  'author' => ['@type' => 'Person', 'name' => 'Felipe Saraiva', 'url' => url('/')],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@if (! $post->published_at?->isPast())
<meta name="robots" content="noindex">
@endif
@endpush
<main class="post">
  <a class="back" href="{{ route('artigos.index') }}">← Artigos</a>
  @if ($post->published_at?->isPast())
    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->isoFormat('LL') }}</time>
  @else
    <time>Pré-visualização — não publicado</time>
  @endif
  <h1>{{ $post->title }}</h1>
  <p class="lead">{{ $post->description }}</p>
  @if ($hasAffiliate)
    <aside class="disclosure">Este artigo contém links de afiliado. Se você comprar por eles, o site pode receber uma comissão — sem custo extra para você.</aside>
  @endif
  <img class="cover" src="{{ $post->coverUrl() }}" alt="">
  <article class="prose">
{!! $html !!}
  </article>
</main>
</x-layouts.artigos>
