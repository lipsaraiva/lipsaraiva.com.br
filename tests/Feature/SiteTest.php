<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attrs = []): Post
    {
        return Post::create($attrs + [
            'title' => 'Review do Fone X', 'slug' => 'review-fone-x', 'description' => 'Resumo.',
            'cover' => 'artigos/capa.jpg', 'body' => 'Texto.', 'published_at' => now()->subDay(),
        ]);
    }

    public function test_markdown_marca_afiliados_botao_e_legenda(): void
    {
        $r = $this->makePost(['body' => implode("\n\n", [
            'Veja o [preço](https://www.amazon.com.br/dp/X) e o [repo](https://github.com/x).',
            '[Ver na loja](https://loja.com/p "afiliado")',
            '![Foto](/img.jpg "Legenda aqui")',
        ])])->rendered();

        $this->assertTrue($r['hasAffiliate']);
        $this->assertStringContainsString('<a href="https://www.amazon.com.br/dp/X" rel="sponsored nofollow noopener" target="_blank">', $r['html']);
        $this->assertStringContainsString('<a href="https://github.com/x" rel="noopener" target="_blank">', $r['html']);
        $this->assertStringContainsString('<p class="aff-btn"><a href="https://loja.com/p" rel="sponsored nofollow noopener" target="_blank">Ver na loja</a></p>', $r['html']);
        $this->assertStringContainsString('<figure><img src="/img.jpg" alt="Foto" loading="lazy"><figcaption>Legenda aqui</figcaption></figure>', $r['html']);
        $this->assertStringNotContainsString('title="afiliado"', $r['html']);
        $this->assertFalse($this->makePost(['slug' => 'b', 'body' => '[x](https://amazon.com.br.evil.com)'])->rendered()['hasAffiliate']);
    }

    public function test_publicado_aparece_e_rascunho_so_para_logado(): void
    {
        $this->makePost();
        $this->makePost(['slug' => 'rascunho', 'title' => 'Rascunho secreto', 'published_at' => null]);
        $this->makePost(['slug' => 'agendado', 'title' => 'Agendado', 'published_at' => now()->addDay()]);

        $this->get('/')->assertOk()->assertSee('Review do Fone X')->assertDontSee('Rascunho secreto');
        $this->get('/artigos')->assertOk()->assertSee('Review do Fone X')->assertDontSee('Rascunho secreto')->assertDontSee('Agendado');
        $this->get('/artigos/review-fone-x')->assertOk()->assertSee('Review do Fone X');
        $this->get('/artigos/rascunho')->assertNotFound();
        $this->get('/artigos/agendado')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertSee('/artigos/review-fone-x')->assertDontSee('/artigos/rascunho');

        $this->actingAs(User::factory()->create())->get('/artigos/rascunho')->assertOk()->assertSee('noindex', false);
    }

    public function test_contato_salva_mensagem_e_honeypot_descarta(): void
    {
        $this->postJson('/contato', ['nome' => 'Ana', 'email' => 'ana@ex.com', 'mensagem' => 'Oi'])->assertOk();
        $this->postJson('/contato', ['nome' => 'Bot', 'email' => 'b@ex.com', 'mensagem' => 'spam', 'website' => 'x'])->assertOk();
        $this->postJson('/contato', ['nome' => '', 'email' => 'invalido'])->assertUnprocessable();

        $this->assertSame(['Ana'], ContactMessage::pluck('name')->all());
    }

    public function test_admin_exige_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
        // logado mas sem 2FA configurado: é obrigado a configurar antes de entrar
        $this->actingAs(User::factory()->create())->get('/admin/artigos')->assertRedirect('/admin/multi-factor-authentication/set-up');
    }
}
