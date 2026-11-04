<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SiteController extends Controller
{
    public function home()
    {
        $posts = Post::published()->take(3)->get()->map(fn (Post $p) => [
            'url' => route('artigos.show', $p),
            'titulo' => $p->title,
            'descricao' => $p->description,
            'capa' => $p->coverUrl(),
            'dataFmt' => $p->published_at->isoFormat('LL'),
        ]);

        return view('home', ['posts' => $posts]);
    }

    public function articles()
    {
        return view('artigos.index', ['posts' => Post::published()->get()]);
    }

    public function article(Post $post)
    {
        // Rascunho/agendado: só quem está logado no painel vê (pré-visualização).
        abort_unless($post->published_at?->isPast() || auth()->check(), 404);

        return view('artigos.show', ['post' => $post, ...$post->rendered()]);
    }

    public function sitemap()
    {
        return response()
            ->view('sitemap', ['posts' => Post::published()->get()])
            ->header('Content-Type', 'application/xml');
    }

    public function contact(Request $request): JsonResponse
    {
        // Honeypot: campo invisível que só robô preenche. Finge sucesso.
        if ($request->filled('website')) {
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'telefone' => ['nullable', 'string', 'max:40'],
            'mensagem' => ['required', 'string', 'max:5000'],
        ]);

        // Salva antes de enviar: se o e-mail falhar, a mensagem continua no painel.
        ContactMessage::create([
            'name' => $data['nome'],
            'email' => $data['email'],
            'phone' => $data['telefone'] ?? null,
            'message' => $data['mensagem'],
            'ip' => $request->ip(),
        ]);

        try {
            Mail::raw(
                "Nome: {$data['nome']}\nE-mail: {$data['email']}\nTelefone: ".($data['telefone'] ?? '—')."\n\n{$data['mensagem']}",
                fn ($m) => $m->to(config('mail.contact_to'))->replyTo($data['email'], $data['nome'])->subject('Contato pelo site — '.$data['nome']),
            );
        } catch (Throwable $e) {
            report($e);
        }

        return response()->json(['ok' => true]);
    }
}
