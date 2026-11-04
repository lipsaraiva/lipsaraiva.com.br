<x-layouts.artigos title="Artigos — SARAIVA" desc="Reviews de tecnologia e artigos sobre web, design e ferramentas.">
<main class="list">
  <div class="label">// Artigos</div>
  <h1>Reviews & tecnologia.</h1>
  @forelse ($posts as $post)
    @if ($loop->first)<div class="grid">@endif
    <a class="card" href="{{ route('artigos.show', $post) }}">
      <div class="card-img"><img src="{{ $post->coverUrl() }}" alt="" loading="lazy"></div>
      <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->isoFormat('LL') }}</time>
      <h2>{{ $post->title }}</h2>
      <p>{{ $post->description }}</p>
    </a>
    @if ($loop->last)</div>@endif
  @empty
    <p class="empty">Os primeiros artigos chegam em breve.</p>
  @endforelse
</main>
</x-layouts.artigos>
