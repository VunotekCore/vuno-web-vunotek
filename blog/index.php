<?php
declare(strict_types=1);

/**
 * Blog Post SSR Renderer
 *
 * Intercepts /blog/{slug} requests that have no static HTML.
 * Queries MySQL directly, renders full page with 200 OK for SEO.
 */

require_once __DIR__ . '/../api/bootstrap.php';

use App\Config\Database;
use App\Models\BlogModel;

// --- Parse slug from REQUEST_URI ---
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$requestUri = rtrim($requestUri, '/');

// Extract slug: /blog/my-post -> my-post, /en/blog/my-post -> my-post
$slug = '';
$locale = 'es';
if (preg_match('#^/(en/)?blog/(.+)$#', $requestUri, $m)) {
    $locale = $m[1] === 'en/' ? 'en' : 'es';
    $slug = $m[2];
}

if ($slug === '') {
    serveNotFound($locale);
    exit;
}

// --- Query DB ---
try {
    $model = new BlogModel(Database::getConnection());
    $post = $model->findBySlug($slug);

    if ($post === null || ($post['status'] ?? '') !== 'published') {
        serveNotFound($locale);
        exit;
    }

    // --- Fetch related posts (same category, max 3) ---
    $related = [];
    if (!empty($post['category_slug'])) {
        $result = $model->list($locale, 'published', 1, 4, $post['category_slug']);
        $allRelated = $result['posts'] ?? [];
        $related = array_values(array_filter(
            $allRelated,
            fn(array $p) => (int)$p['id'] !== (int)$post['id']
        ));
        $related = array_slice($related, 0, 3);
    }

    // --- Render full page with 200 ---
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, max-age=300');

    $isEn = $locale === 'en';
    $title = $post['meta_title']
        ? ($post['meta_title'] . ' | Vunotek')
        : ($post['title'] . ' | Vunotek');
    $description = $post['excerpt'] ?: $post['title'];
    $backLabel = $isEn ? 'Back to blog' : 'Volver al blog';
    $relatedTitle = $isEn ? 'Related Articles' : 'Artículos relacionados';
    $backUrl = $isEn ? '/en/blog' : '/blog';
    $author = htmlspecialchars($post['author'] ?: 'Daniel Flores', ENT_QUOTES, 'UTF-8');
    $dateFormatted = date('M d, Y', strtotime($post['created_at']));
    $postTitle = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
    $postExcerpt = htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8');
    $postImage = htmlspecialchars($post['image'] ?? '', ENT_QUOTES, 'UTF-8');

    // Related posts HTML
    $relatedHtml = '';
    if (!empty($related)) {
        $cards = '';
        foreach ($related as $rp) {
            $rpSlug = htmlspecialchars($rp['slug'], ENT_QUOTES, 'UTF-8');
            $rpTitle = htmlspecialchars($rp['title'], ENT_QUOTES, 'UTF-8');
            $rpExcerpt = htmlspecialchars($rp['excerpt'], ENT_QUOTES, 'UTF-8');
            $rpCat = htmlspecialchars($rp['category_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $rpDate = date('M d, Y', strtotime($rp['created_at']));
            $rpLink = ($isEn ? '/en' : '') . "/blog/{$rpSlug}/";
            $rpImg = htmlspecialchars($rp['image'] ?? '', ENT_QUOTES, 'UTF-8');

            $imgTag = $rpImg
                ? '<img src="' . $rpImg . '" alt="' . $rpTitle . '" width="640" height="400" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">'
                : '<span class="material-symbols-outlined text-6xl text-outline/30">description</span>';

            $cards .= <<<HTML
<article class="group cursor-pointer">
  <a href="{$rpLink}" class="block">
    <div class="aspect-[16/10] glass-panel rounded-xl mb-md flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:border-electric-blue/50">{$imgTag}</div>
    <div class="flex items-center gap-2 mb-sm">
      <span class="font-label-mono text-[11px] text-electric-blue bg-electric-blue/10 px-2 py-0.5 rounded">{$rpCat}</span>
      <span class="font-label-mono text-[11px] text-slate-text">{$rpDate}</span>
    </div>
    <h4 class="font-headline-md text-headline-md max-sm:text-headline-sm text-on-surface group-hover:text-electric-blue transition-colors mb-xs">{$rpTitle}</h4>
    <p class="font-body-md text-body-md text-slate-text line-clamp-2">{$rpExcerpt}</p>
  </a>
</article>
HTML;
        }

        $relatedSection = <<<HTML
<div class="mt-xl">
  <h2 class="font-display-lg text-headline-lg text-on-surface mb-lg">{$relatedTitle}</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-md">{$cards}</div>
</div>
HTML;
    } else {
        $relatedSection = '';
    }

    // Hero image
    $heroImage = '';
    if ($postImage) {
        $heroImage = <<<HTML
<div class="aspect-[16/9] md:aspect-[21/9] glass-panel rounded-xl mt-xl overflow-hidden">
  <img src="{$postImage}" alt="{$postTitle}" width="1200" height="675" class="w-full h-full object-cover" loading="eager">
</div>
HTML;
    }

    require __DIR__ . '/layout.php';

    echo renderHead($title, $description, $locale, $slug, null, $post);
    echo renderNavbar($locale);
    echo '<main id="main-content">';
    echo '<section class="py-lg md:py-xl !pt-32 md:!pt-40 !pb-lg md:!pb-xl" style="scroll-margin-top: 80px;">';
    echo '<div class="max-w-[min(90vw,1600px)] mx-auto px-margin">';
    echo '<a href="' . $backUrl . '" class="inline-flex items-center gap-xs text-electric-blue font-label-mono hover:underline mb-lg">';
    echo '<span class="material-symbols-outlined text-sm">arrow_back</span>';
    echo htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8');
    echo '</a>';
    echo '</div></section>';
    echo '<section class="py-lg md:py-xl bg-surface-container-lowest border-y border-outline-variant/10" style="scroll-margin-top: 80px;">';
    echo '<div class="max-w-[min(90vw,1600px)] mx-auto px-margin">';

    // Meta bar
    $catName = htmlspecialchars($post['category_name'] ?? '', ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<div class="flex flex-wrap items-center gap-3 mb-md">
  <span class="font-label-mono text-[11px] text-electric-blue bg-electric-blue/10 px-3 py-1 rounded">{$catName}</span>
  <span class="font-label-mono text-[11px] text-slate-text">{$dateFormatted}</span>
  <span class="font-label-mono text-[11px] text-slate-text/60">— {$author}</span>
</div>
HTML;

    // Title
    echo '<h1 class="font-display-lg text-headline-lg-mobile md:text-headline-lg text-on-surface max-w-4xl">' . $postTitle . '</h1>';

    // Hero image
    echo $heroImage;

    // Content
    echo '<div class="max-w-3xl mx-auto mt-xl">';
    echo '<div class="blog-content">' . $post['content'] . '</div>';
    echo '</div>';

    // Related
    echo $relatedSection;

    echo '</div></section>';
    echo '</main>';
    echo renderFooter($locale);
    echo '</body></html>';
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Internal Server Error</h1>';
    if ((getenv('APP_DEBUG') ?: '') === 'true') {
        echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    }
    exit;
}


function serveNotFound(string $locale): void {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');

    $isEn = $locale === 'en';
    $title = $isEn ? 'Page Not Found | Vunotek' : 'Página no encontrada | Vunotek';
    $description = $isEn ? 'The page you are looking for does not exist.' : 'La página que buscas no existe.';

    require __DIR__ . '/layout.php';
    echo renderHead($title, $description, $locale);
    echo renderNavbar($locale);
    echo renderNotFound($locale);
    echo renderFooter($locale);
    echo '</body></html>';
}
