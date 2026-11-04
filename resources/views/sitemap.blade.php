{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>{{ url('/') }}</loc></url>
  <url><loc>{{ route('artigos.index') }}</loc></url>
@foreach ($posts as $post)
  <url><loc>{{ route('artigos.show', $post) }}</loc><lastmod>{{ $post->updated_at->toDateString() }}</lastmod></url>
@endforeach
</urlset>
