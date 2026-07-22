<?php
declare(strict_types=1);

/**
 * Blog SSR Layout — HTML shell matching Astro's BaseLayout.
 */

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function renderHead(
    string $title,
    string $description,
    string $locale,
    string $slug = '',
    ?string $ogImage = null,
    ?array $post = null
): string {
    $lang = $locale === 'en' ? 'en-US' : 'es-NI';
    $siteUrl = 'https://vunotek.com';
    $baseUrl = $locale === 'en' ? "$siteUrl/en" : $siteUrl;
    $pageUrl = $slug ? "$baseUrl/blog/$slug/" : "$baseUrl/blog/";
    $canonical = $pageUrl;
    $ogDefault = "$siteUrl/logo.webp";
    $ogImg = $ogImage ?: $ogDefault;
    $titleEsc = e($title);
    $descEsc = e($description);

    $hreflangEs = $slug ? '<link rel="alternate" hreflang="es" href="' . $siteUrl . '/blog/' . e($slug) . '/">' : '';
    $hreflangEn = $slug ? '<link rel="alternate" hreflang="en" href="' . $siteUrl . '/en/blog/' . e($slug) . '/">' : '';
    $hreflangX  = $slug ? '<link rel="alternate" hreflang="x-default" href="' . $siteUrl . '/blog/' . e($slug) . '/">' : '';

    $jsonLd = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Vunotek',
            'url' => $siteUrl,
            'logo' => ['@type' => 'ImageObject', 'url' => "$siteUrl/logo.webp"],
            'description' => 'Software Infrastructure & Advanced Automation',
            'sameAs' => [],
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Vunotek',
            'url' => $siteUrl,
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $locale === 'en' ? 'Home' : 'Inicio', 'item' => $baseUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $baseUrl . '/blog/'],
            ],
        ],
    ];

    if ($post) {
        $author = e($post['author'] ?? 'Daniel Flores');
        $postTitle = e($post['meta_title'] ?: $post['title']);
        $postDesc = e($post['excerpt']);
        $postImg = $post['og_image'] ?: ($post['image'] ?: $ogDefault);
        $postUrl = $siteUrl . ($locale === 'en' ? '/en' : '') . '/blog/' . e($post['slug']) . '/';
        $datePublished = e($post['created_at'] ?? '');

        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $postTitle,
            'description' => $postDesc,
            'author' => ['@type' => 'Person', 'name' => $author, 'url' => "$siteUrl/about"],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Vunotek',
                'logo' => ['@type' => 'ImageObject', 'url' => "$siteUrl/logo.webp"],
            ],
            'datePublished' => $datePublished,
            'dateModified' => $datePublished,
            'image' => e($postImg),
            'url' => $postUrl,
        ];

        $ogImg = $post['og_image'] ?: ($post['image'] ?: $ogDefault);
    }

    $jsonLdStr = json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $gaId = getenv('PUBLIC_GA_ID') ?: '';
    $gaScript = '';
    if ($gaId !== '' && $gaId !== false) {
        $gaScript = <<<HTML
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$gaId}');</script>
<script src="https://www.googletagmanager.com/gtag/js?id={$gaId}" async></script>
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html class="dark" lang="{$lang}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0b1326">
<title>{$titleEsc}</title>
<meta name="description" content="{$descEsc}">
<meta name="robots" content="index, follow, max-snippet:150, max-image-preview:large">
<link rel="canonical" href="{$canonical}">
{$hreflangEs}
{$hreflangEn}
{$hreflangX}
<meta property="og:locale" content="{$lang}">
<meta property="og:title" content="{$titleEsc}">
<meta property="og:description" content="{$descEsc}">
<meta property="og:type" content="article">
<meta property="og:url" content="{$pageUrl}">
<meta property="og:image" content="{$ogImg}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="Vunotek">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@vunotek">
<meta name="twitter:title" content="{$titleEsc}">
<meta name="twitter:description" content="{$descEsc}">
<meta name="twitter:image" content="{$ogImg}">
<meta name="google-site-verification" content="OLAPWz6sRxKAUk91oJG0dQmfTtv-mFFGoHo-c_wG3DA">
<?php if ($post): ?>
<meta property="article:published_time" content="<?= e($post['created_at'] ?? '') ?>">
<meta property="article:author" content="<?= e($post['author'] ?? 'Daniel Flores') ?>">
<meta property="article:section" content="<?= e($post['category_name'] ?? 'Blog') ?>">
<?php endif; ?>
<link rel="icon" type="image/svg+xml" href="/vunotek-isotipo-square.svg">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="preconnect" href="https://ik.imagekit.io">
<link rel="stylesheet" href="/blog/assets/fonts.css">
<link rel="stylesheet" href="/blog/assets/style.css">
<script type="application/ld+json">{$jsonLdStr}</script>
{$gaScript}
</head>
<body class="bg-background text-on-surface font-body-md selection:bg-electric-blue/30 selection:text-electric-blue overflow-x-hidden">
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:px-4 focus:py-2 focus:bg-surface-charcoal focus:text-electric-blue focus:outline-none">Skip to main content</a>
HTML;
}


function renderNavbar(string $locale): string {
    $prefix = $locale === 'en' ? '/en' : '';
    $p = function(string $path) use ($locale) {
        return $locale === 'en' ? "/en$path" : $path;
    };

    $links = [
        [$p('/'), $locale === 'en' ? 'Solutions' : 'Soluciones'],
        [$p('/servicios'), $locale === 'en' ? 'Services' : 'Servicios'],
        [$p('/proyectos'), $locale === 'en' ? 'Stack' : 'Stack'],
        [$p('/blog'), 'Blog'],
        [$p('/about'), $locale === 'en' ? 'About' : 'Nosotros'],
        [$p('/contacto'), $locale === 'en' ? 'Contact' : 'Contacto'],
    ];

    $currentPath = $_SERVER['REQUEST_URI'] ?? '/blog/';
    $navLinks = '';
    $mobileLinks = '';
    foreach ($links as [$href, $label]) {
        $hrefEsc = e($href);
        $labelEsc = e($label);
        $isActive = str_starts_with($currentPath, $href) && $href !== '/';
        $activeClass = $isActive
            ? 'text-electric-blue font-bold border-b-2 border-electric-blue pb-1'
            : 'text-on-surface-variant hover:text-electric-blue';
        $mobileActive = $isActive
            ? 'text-electric-blue font-bold'
            : 'text-on-surface-variant hover:text-electric-blue';

        $navLinks .= "<a href=\"{$hrefEsc}\" class=\"font-body-md text-body-md transition-colors duration-200 {$activeClass}\">{$labelEsc}</a>";
        $mobileLinks .= "<a href=\"{$hrefEsc}\" class=\"font-body-md text-body-md py-2 transition-colors {$mobileActive}\">{$labelEsc}</a>";
    }

    $switchHref = $locale === 'en' ? e($prefix . str_replace('/en', '', $currentPath)) : e('/en' . $currentPath);
    $switchLabel = $locale === 'en' ? 'ES' : 'EN';
    $switchAria = $locale === 'en' ? 'Switch to ES' : 'Switch to EN';
    $getStartedLabel = $locale === 'en' ? 'Get Started' : 'Comenzar';

    $svgLogo = file_get_contents(__DIR__ . '/assets/logo-svg.txt');
    $homeHref = $p('/');

    $navbarJs = <<<'JS'
const navBar=document.getElementById("navbar"),menuBtn=document.getElementById("menu-toggle"),mobileMenu=document.getElementById("mobile-menu");
menuBtn.addEventListener("click",()=>{mobileMenu.classList.toggle("hidden");const useEl=menuBtn.querySelector("use");useEl&&useEl.setAttribute("href",`/sprite.svg#${mobileMenu.classList.contains("hidden")?"menu":"close"}`)});
let ticking=false;window.addEventListener("scroll",()=>{ticking||(window.requestAnimationFrame(()=>{window.scrollY>50?(navBar.classList.add("h-16","bg-surface/95"),navBar.classList.remove("h-20")):(navBar.classList.remove("h-16","bg-surface/95"),navBar.classList.add("h-20")),ticking=false}),ticking=true)});
JS;

    return <<<HTML
<nav class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md border-b border-outline-variant/30 shadow-sm transition-all duration-300 ease-in-out h-20" id="navbar">
  <div class="flex justify-between items-center px-margin max-w-[min(90vw,1600px)] mx-auto h-full">
    <a href="{$homeHref}" class="flex items-center">{$svgLogo}</a>
    <div class="flex items-center gap-base">
      <div class="hidden min-[1190px]:flex items-center gap-lg">{$navLinks}</div>
      <a href="{$switchHref}" class="inline-flex items-center gap-1.5 font-label-mono text-label-mono text-on-surface-variant hover:text-electric-blue transition-colors px-2 py-1 ml-4" aria-label="{$switchAria}">{$switchLabel}</a>
      <button class="min-[1190px]:hidden text-on-surface p-2" aria-label="Toggle menu" id="menu-toggle">
        <svg class="text-2xl" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><use href="/sprite.svg#menu"></use></svg>
      </button>
    </div>
  </div>
  <div id="mobile-menu" class="hidden min-[1190px]:hidden bg-surface/95 backdrop-blur-md border-t border-outline-variant/20">
    <div class="px-margin py-md flex flex-col items-center text-center gap-base">
      {$mobileLinks}
      <a href="{$p('/contacto')}" class="bg-electric-blue text-azure-deep px-6 py-3 rounded-lg font-semibold text-center mt-sm">{$getStartedLabel}</a>
    </div>
  </div>
</nav>
<script>{$navbarJs}</script>
HTML;
}


function renderFooter(string $locale): string {
    $p = function(string $path) use ($locale) {
        return $locale === 'en' ? "/en$path" : $path;
    };

    $navTitle = $locale === 'en' ? 'Navigation' : 'Navegación';
    $legalTitle = 'Legal';
    $techTitle = 'Core Infrastructure:';
    $copyright = '© 2026 Vunotek. ' . ($locale === 'en' ? 'All rights reserved.' : 'Todos los derechos reservados.');
    $version = 'V1.0.0-RELEASE';
    $origin = 'ENGINEERED WITH VUE';
    $description = $locale === 'en'
        ? 'Software infrastructure and advanced automation. We design stable systems and high-performance modular enterprise solutions.'
        : 'Infraestructura de software y automatización avanzada. Diseñamos sistemas estables y soluciones modulares de alto rendimiento empresarial.';
    $privacyLabel = $locale === 'en' ? 'Privacy Policy' : 'Política de Privacidad';
    $termsLabel = $locale === 'en' ? 'Terms of Service' : 'Términos de Servicio';

    $navLinks = [
        [$p('/proyectos'), $locale === 'en' ? 'Solutions' : 'Soluciones'],
        [$p('/servicios'), $locale === 'en' ? 'Services' : 'Servicios'],
        [$p('/blog'), 'Blog'],
        [$p('/about'), $locale === 'en' ? 'About' : 'Nosotros'],
        [$p('/contacto'), $locale === 'en' ? 'Contact' : 'Contacto'],
    ];
    $navHtml = '';
    foreach ($navLinks as [$href, $label]) {
        $navHtml .= '<a href="' . e($href) . '" class="font-body-md text-body-md text-slate-text hover:text-electric-blue transition-colors">' . e($label) . '</a>';
    }

    $techItems = ['Vue.js', 'Node.js', '.NET', 'AWS', 'PostgreSQL', 'Tailwind CSS'];
    $techHtml = '';
    foreach ($techItems as $tech) {
        $techHtml .= '<span class="font-label-mono text-label-mono text-electric-blue/80 bg-electric-blue/8 border border-electric-blue/15 px-2.5 py-0.5 rounded text-xs">' . e($tech) . '</span>';
    }

    $svgLogo = file_get_contents(__DIR__ . '/assets/logo-svg-small.txt');
    $homeHref = $p('/');

    return <<<HTML
<footer class="border-t border-outline-variant/20">
  <div class="max-w-[min(90vw,1600px)] mx-auto px-margin">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-lg py-lg md:py-xl">
      <div class="md:col-span-5 flex flex-col gap-md max-sm:items-center max-sm:text-center">
        <a href="{$homeHref}" class="flex items-center">{$svgLogo}</a>
        <p class="font-body-md text-body-md text-slate-text">{$description}</p>
      </div>
      <div class="md:col-span-4 flex flex-col gap-base max-sm:items-center max-sm:text-center">
        <span class="font-label-mono text-label-mono text-on-surface uppercase tracking-widest text-xs">{$navTitle}</span>
        <div class="grid grid-cols-2 gap-x-md gap-y-sm">{$navHtml}</div>
      </div>
      <div class="md:col-span-3 flex flex-col gap-base max-sm:items-center max-sm:text-center">
        <span class="font-label-mono text-label-mono text-on-surface uppercase tracking-widest text-xs">{$legalTitle}</span>
        <div class="flex flex-col gap-sm">
          <a href="{$p('/privacidad/')}" class="font-body-md text-body-md text-slate-text hover:text-electric-blue transition-colors">{$privacyLabel}</a>
          <a href="{$p('/terminos/')}" class="font-body-md text-body-md text-slate-text hover:text-electric-blue transition-colors">{$termsLabel}</a>
        </div>
      </div>
    </div>
    <div class="flex flex-col sm:flex-row max-sm:items-center max-sm:text-center items-start sm:items-center gap-sm py-md border-y border-outline-variant/10">
      <span class="font-label-mono text-label-mono text-slate-text uppercase tracking-widest text-xs flex-shrink-0">{$techTitle}</span>
      <div class="flex flex-wrap gap-sm">{$techHtml}</div>
    </div>
    <div class="flex flex-col sm:flex-row justify-between items-center gap-md py-md">
      <p class="font-body-md text-body-md text-slate-text/90 text-xs">{$copyright}</p>
      <div class="flex items-center gap-md">
        <span class="font-label-mono text-label-mono text-electric-blue/80 text-xs">{$version}</span>
        <span class="w-px h-3 bg-outline-variant/20"></span>
        <span class="font-label-mono text-label-mono text-slate-text/75 text-xs">{$origin}</span>
      </div>
    </div>
  </div>
</footer>
HTML;
}


function renderNotFound(string $locale): string {
    $isEn = $locale === 'en';
    $message = $isEn
        ? 'The page you are looking for does not exist or has been moved.'
        : 'La página que buscas no existe o fue movida.';
    $backLink = $isEn ? 'Back to home' : 'Volver al inicio';
    $homeUrl = $isEn ? '/en/' : '/';

    return <<<HTML
<main id="main-content">
  <div class="min-h-screen flex items-center justify-center">
    <div class="flex flex-col items-center gap-md text-center px-margin">
      <a href="{$homeUrl}" class="flex items-center gap-3 mb-sm">
        <span class="font-display-lg text-headline-md font-bold tracking-tight"><span class="text-on-surface">VUNO</span><span class="text-electric-blue">TEK</span></span>
      </a>
      <h1 class="font-display-lg text-headline-lg text-on-surface">404</h1>
      <p class="font-body-lg text-body-lg text-slate-text">{$message}</p>
      <a href="{$homeUrl}" class="font-label-mono text-electric-blue hover:underline">{$backLink}</a>
    </div>
  </div>
</main>
HTML;
}
