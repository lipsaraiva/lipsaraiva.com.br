# lipsaraiva.com.br

Site + painel de artigos em Laravel 13 + Filament 5 (SQLite). Deploy automático para o VPS a cada push na `main`.

- `/` — home (`resources/views/home.blade.php`, runtime `public/support.js`)
- `/artigos` — artigos publicados · `/sitemap.xml`
- `/admin` — painel (login + 2FA obrigatório): artigos e mensagens do formulário de contato

## Rodar local

```sh
composer install
cp .env.example .env   # depois troque APP_ENV=local, APP_DEBUG=true, APP_URL=http://localhost:8000, SESSION_SECURE_COOKIE=false, MAIL_MAILER=log
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan admin:criar
php artisan serve
php artisan test
```

## Usuário do painel

Não há cadastro público. Crie pelo terminal (no servidor, via SSH):

```sh
php artisan admin:criar
```

No primeiro login o painel obriga a configurar o 2FA (Google Authenticator, 1Password etc.). Guarde os códigos de recuperação.

## Artigos

Texto em Markdown. Links da Amazon, Mercado Livre, Shopee, AliExpress, Magalu e KaBuM viram afiliado sozinhos (`rel="sponsored"` + aviso no topo do artigo) — lista em `app/Models/Post.php`. Outro programa: `[texto](url "afiliado")`. Link de afiliado sozinho num parágrafo vira botão.

Sem data de publicação = rascunho; data futura = agendado. Rascunhos abrem para quem está logado (botão "Ver no site").

## Servidor

- Raiz do site (docRoot) **precisa** apontar para `public_html/public`.
- PHP 8.3 com `pdo_sqlite`, `intl`, `mbstring`, `fileinfo`; Composer instalado.
- Segredos só no `.env` do servidor (e-mail SMTP em `MAIL_*`). Depois de editar: `php artisan optimize`.
- Backup: `database/database.sqlite` e `storage/app/public/`.
