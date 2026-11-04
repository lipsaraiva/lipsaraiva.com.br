// Gera /artigos a partir de posts/*.md. Uso: npm run build
// Arquivos começando com "_" (ex.: _modelo.md) são ignorados — use para rascunhos.
import fs from 'node:fs';
import assert from 'node:assert';
import { Marked } from 'marked';

const SITE = 'https://lipsaraiva.com.br';
const AUTHOR = 'Felipe Saraiva';
const OUT = 'artigos';

// Links para estes domínios viram links de afiliado (rel="sponsored").
// Para outros programas, use o título "afiliado": [texto](url "afiliado")
const AFFILIATE_HOSTS = [
  'amazon.com.br', 'amazon.com', 'amzn.to', 'mercadolivre.com.br', 'mercadolivre.com', 'meli.la',
  'shopee.com.br', 'aliexpress.com', 'magazineluiza.com.br', 'magalu.com', 'kabum.com.br',
];

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const abs = (u) => (u.startsWith('/') ? SITE + u : u);
const fmtDate = (d) => new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeZone: 'UTC' }).format(new Date(d));

function isAffiliate(href, title) {
  if (title === 'afiliado') return true;
  try {
    const host = new URL(href).hostname.replace(/^www\./, '');
    return AFFILIATE_HOSTS.some((h) => host === h || host.endsWith('.' + h));
  } catch { return false; }
}
assert(isAffiliate('https://www.amazon.com.br/dp/X') && isAffiliate('https://s.shopee.com.br/abc'));
assert(isAffiliate('https://loja.com/x', 'afiliado') && !isAffiliate('https://github.com') && !isAffiliate('/artigos/'));

function parsePost(file) {
  const raw = fs.readFileSync('posts/' + file, 'utf8');
  const m = raw.match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n?([\s\S]*)$/);
  if (!m) throw new Error(file + ': falta o cabeçalho entre --- no topo');
  const meta = {};
  for (const line of m[1].split(/\r?\n/)) {
    const i = line.indexOf(':');
    if (i > 0) meta[line.slice(0, i).trim()] = line.slice(i + 1).trim().replace(/^["']|["']$/g, '');
  }
  for (const k of ['titulo', 'descricao', 'data', 'capa']) {
    if (!meta[k]) throw new Error(file + ': campo "' + k + '" é obrigatório');
  }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(meta.data)) throw new Error(file + ': data deve ser AAAA-MM-DD');

  let hasAffiliate = false;
  const md = new Marked({
    renderer: {
      link({ href, title, tokens }) {
        const text = this.parser.parseInline(tokens);
        if (isAffiliate(href, title)) {
          hasAffiliate = true;
          return `<a href="${esc(href)}" rel="sponsored nofollow noopener" target="_blank">${text}</a>`;
        }
        const ext = /^https?:/.test(href) ? ' rel="noopener" target="_blank"' : '';
        return `<a href="${esc(href)}"${title ? ` title="${esc(title)}"` : ''}${ext}>${text}</a>`;
      },
      // Link de afiliado sozinho num parágrafo vira botão.
      paragraph({ tokens }) {
        const html = this.parser.parseInline(tokens);
        const solo = tokens.length === 1 && tokens[0].type === 'link' && isAffiliate(tokens[0].href, tokens[0].title);
        return solo ? `<p class="aff-btn">${html}</p>\n` : `<p>${html}</p>\n`;
      },
      image({ href, title, text }) {
        const img = `<img src="${esc(href)}" alt="${esc(text)}" loading="lazy">`;
        return title ? `<figure>${img}<figcaption>${esc(title)}</figcaption></figure>` : img;
      },
    },
  });
  const body = md.parse(m[2]);
  return { ...meta, slug: file.replace(/\.md$/, ''), url: `/${OUT}/${file.replace(/\.md$/, '')}/`, body, hasAffiliate };
}

const head = ({ title, desc, url, image, type = 'website' }) => `<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(title)}</title>
<meta name="description" content="${esc(desc)}">
<link rel="canonical" href="${SITE}${url}">
<meta property="og:type" content="${type}">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="SARAIVA">
<meta property="og:title" content="${esc(title)}">
<meta property="og:description" content="${esc(desc)}">
<meta property="og:url" content="${SITE}${url}">
${image ? `<meta property="og:image" content="${esc(abs(image))}">\n<meta name="twitter:card" content="summary_large_image">` : ''}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/artigos.css">`;

const header = `<header class="top">
  <a href="/" class="logo">SARAIVA</a>
  <nav><a href="/${OUT}/">Artigos</a><a href="/#contato">Contato</a></nav>
</header>`;

const footer = `<footer class="foot">
  <span>© ${new Date().getFullYear()} Saraiva · Todos os direitos reservados</span>
  <a href="/">lipsaraiva.com.br</a>
</footer>
</body>
</html>
`;

const card = (p) => `<a class="card" href="${p.url}">
      <div class="card-img"><img src="${esc(p.capa)}" alt="" loading="lazy"></div>
      <time datetime="${p.data}">${fmtDate(p.data)}</time>
      <h2>${esc(p.titulo)}</h2>
      <p>${esc(p.descricao)}</p>
    </a>`;

const postPage = (p) => `${head({ title: p.titulo + ' — SARAIVA', desc: p.descricao, url: p.url, image: p.capa, type: 'article' })}
<meta property="article:published_time" content="${p.data}">
<script type="application/ld+json">${JSON.stringify({
  '@context': 'https://schema.org', '@type': 'BlogPosting',
  headline: p.titulo, description: p.descricao, image: abs(p.capa),
  datePublished: p.data, mainEntityOfPage: SITE + p.url,
  author: { '@type': 'Person', name: AUTHOR, url: SITE },
}).replace(/</g, '\\u003c')}</script>
</head>
<body>
${header}
<main class="post">
  <a class="back" href="/${OUT}/">← Artigos</a>
  <time datetime="${p.data}">${fmtDate(p.data)}</time>
  <h1>${esc(p.titulo)}</h1>
  <p class="lead">${esc(p.descricao)}</p>
  ${p.hasAffiliate ? '<aside class="disclosure">Este artigo contém links de afiliado. Se você comprar por eles, o site pode receber uma comissão — sem custo extra para você.</aside>' : ''}
  <img class="cover" src="${esc(p.capa)}" alt="">
  <article class="prose">
${p.body}
  </article>
</main>
${footer}`;

const listPage = (posts) => `${head({ title: 'Artigos — SARAIVA', desc: 'Reviews de tecnologia e artigos sobre web, design e ferramentas.', url: `/${OUT}/` })}
</head>
<body>
${header}
<main class="list">
  <div class="label">// Artigos</div>
  <h1>Reviews & tecnologia.</h1>
  ${posts.length ? `<div class="grid">\n    ${posts.map(card).join('\n    ')}\n  </div>` : '<p class="empty">Os primeiros artigos chegam em breve.</p>'}
</main>
${footer}`;

const posts = fs.readdirSync('posts')
  .filter((f) => f.endsWith('.md') && !f.startsWith('_'))
  .map(parsePost)
  .sort((a, b) => b.data.localeCompare(a.data));

// /artigos é 100% gerado: apaga e recria para não sobrar página de post removido.
fs.rmSync(OUT, { recursive: true, force: true });
fs.mkdirSync(OUT);
fs.writeFileSync(`${OUT}/index.html`, listPage(posts));
for (const p of posts) {
  fs.mkdirSync(`${OUT}/${p.slug}`);
  fs.writeFileSync(`${OUT}/${p.slug}/index.html`, postPage(p));
}
// Lido pela home (seção "Últimos artigos").
fs.writeFileSync(`${OUT}/latest.json`, JSON.stringify(posts.slice(0, 3).map((p) => ({
  url: p.url, titulo: p.titulo, descricao: p.descricao, capa: p.capa, dataFmt: fmtDate(p.data),
}))));
fs.writeFileSync('sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>${SITE}/</loc></url>
  <url><loc>${SITE}/${OUT}/</loc></url>
${posts.map((p) => `  <url><loc>${SITE}${p.url}</loc><lastmod>${p.data}</lastmod></url>`).join('\n')}
</urlset>
`);
console.log(`${posts.length} artigo(s) gerado(s) em /${OUT}`);
