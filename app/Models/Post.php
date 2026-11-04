<?php

namespace App\Models;

use DOMDocument;
use DOMElement;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'description', 'cover', 'body', 'published_at'])]
class Post extends Model
{
    // Links para estes domínios viram links de afiliado (rel="sponsored").
    // Para outros programas, use o título "afiliado": [texto](url "afiliado")
    public const AFFILIATE_HOSTS = [
        'amazon.com.br', 'amazon.com', 'amzn.to', 'mercadolivre.com.br', 'mercadolivre.com', 'meli.la',
        'shopee.com.br', 'aliexpress.com', 'magazineluiza.com.br', 'magalu.com', 'kabum.com.br',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at');
    }

    public function coverUrl(): string
    {
        return Storage::disk('public')->url($this->cover);
    }

    public static function isAffiliate(string $href, ?string $title = null): bool
    {
        if ($title === 'afiliado') {
            return true;
        }
        $host = preg_replace('/^www\./', '', (string) parse_url($href, PHP_URL_HOST));

        return $host !== '' && collect(self::AFFILIATE_HOSTS)->contains(fn ($h) => $host === $h || str_ends_with($host, '.'.$h));
    }

    /**
     * Markdown → HTML, com links de afiliado marcados, link de afiliado sozinho
     * num parágrafo virando botão, e imagem com título virando <figure>.
     *
     * @return array{html: string, hasAffiliate: bool}
     */
    public function rendered(): array
    {
        $html = Str::markdown($this->body, ['html_input' => 'allow', 'allow_unsafe_links' => false]);

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $hasAffiliate = false;
        foreach (iterator_to_array($doc->getElementsByTagName('a')) as $a) {
            /** @var DOMElement $a */
            $href = $a->getAttribute('href');
            if (self::isAffiliate($href, $a->getAttribute('title'))) {
                $hasAffiliate = true;
                if ($a->getAttribute('title') === 'afiliado') {
                    $a->removeAttribute('title');
                }
                $a->setAttribute('rel', 'sponsored nofollow noopener');
                $a->setAttribute('target', '_blank');
                if (self::isAloneInParagraph($a)) {
                    $a->parentNode->setAttribute('class', 'aff-btn');
                }
            } elseif (preg_match('#^https?://#', $href)) {
                $a->setAttribute('rel', 'noopener');
                $a->setAttribute('target', '_blank');
            }
        }

        foreach (iterator_to_array($doc->getElementsByTagName('img')) as $img) {
            /** @var DOMElement $img */
            $img->setAttribute('loading', 'lazy');
            if ($caption = $img->getAttribute('title')) {
                $img->removeAttribute('title');
                $figure = $doc->createElement('figure');
                // imagem sozinha num parágrafo: o <figure> substitui o <p>
                $target = self::isAloneInParagraph($img) ? $img->parentNode : $img;
                $target->parentNode->replaceChild($figure, $target);
                $figure->appendChild($img);
                $figure->appendChild($doc->createElement('figcaption', htmlspecialchars($caption)));
            }
        }

        $root = $doc->getElementById('root');
        $out = '';
        foreach ($root->childNodes as $node) {
            $out .= $doc->saveHTML($node);
        }

        return ['html' => $out, 'hasAffiliate' => $hasAffiliate];
    }

    private static function isAloneInParagraph(DOMElement $el): bool
    {
        $p = $el->parentNode;
        if (! $p instanceof DOMElement || $p->tagName !== 'p') {
            return false;
        }
        foreach ($p->childNodes as $node) {
            if ($node !== $el && trim($node->textContent) !== '') {
                return false;
            }
        }

        return true;
    }
}
