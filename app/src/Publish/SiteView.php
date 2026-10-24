<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\App;
use App\Core\Countries;
use App\Core\Lang;
use App\Core\View;
use App\Service\Turnstile;

/**
 * What a site template sees as $v: one language, the site data and small HTML helpers.
 * Templates live in templates/site/; they are written on several lines for reading, and partial() removes the
 * line breaks between tags again, so the pages come out as compact as the ones build.py made.
 */
final class SiteView
{
    public const SERVICES = [['srv1', 'tool'], ['srv2', 'shield'], ['srv3', 'cap'], ['srv4', 'box']];
    public const WHY = [['why1', 'award'], ['why2', 'users'], ['why3', 'tool'], ['why4', 'box']];

    private static array $icons;
    private const BRANDS = [
        'whatsapp' => 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z',
        'phone' => 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z',
        'gmail' => 'M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 010 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z',
        'facebook' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
    ];
    private static array $imgSize = [];
    private static array $assetVersion = [];

    public readonly string $lang;
    /** prefix that turns a path under public/ into a link from public/<lang>/ */
    private string $root;
    private array $meta = [];
    /** JSON-LD objects for this page, set by the page template through schema() */
    private array $ld = [];

    public function __construct(
        private Lang $l,
        public readonly SiteData $data,
        private string $publicDir,
        public readonly string $a = '../assets',
        public readonly bool $preview = false,
        /** full origin (https://xtklaos.com) for canonical, hreflang and JSON-LD; null = relative links only */
        private ?string $baseUrl = null,
    ) {
        $this->lang = $l->code;
        $this->root = substr($a, 0, -strlen('assets'));
        self::$icons ??= require App::path('templates/site/partials/icons.php');
    }

    // ------------------------------------------------------------ text
    public function t(string $key): string
    {
        return $this->l->t($key);
    }

    public function f(string $key, string|int ...$args): string
    {
        return $this->l->f($key, ...$args);
    }

    /** Translated column of a row, escaped for HTML. */
    public function tr(array $row, string $field): string
    {
        return $this->e($this->l->tr($row, $field));
    }

    /** Translated column of a row, not escaped (for attributes that escape themselves, and stored HTML). */
    public function raw(array $row, string $field): string
    {
        return $this->l->tr($row, $field);
    }

    public function e(string|int|null $s): string
    {
        // same entity for ' as build.py (Python's html.escape), so pages stay byte-comparable
        return str_replace('&#039;', '&#x27;', e($s));
    }

    public function fdate(string $iso): string
    {
        [$y, $m, $d] = explode('-', $iso);
        return $this->lang === 'zh' ? sprintf('%s 年 %d 月 %d 日', $y, (int) $m, (int) $d) : "{$d}/{$m}/{$y}";
    }

    // ------------------------------------------------------------ HTML pieces
    public function partial(string $name, array $vars = []): string
    {
        return self::compact(View::render('site/partials/' . $name, ['v' => $this] + $vars));
    }

    public function page(string $name, array $vars = []): string
    {
        return self::compact(View::render('site/pages/' . $name, ['v' => $this] + $vars));
    }

    /**
     * Removes the template's indentation between tags: any gap with a line break, or of 2+ spaces (PHP drops the
     * newline after "?>", which leaves only the next line's indent). A single space between tags is meant and kept.
     */
    public static function compact(string $html): string
    {
        return (string) preg_replace('/>(?:\s*\n\s*| {2,})</', '><', trim($html));
    }

    public function ic(string $name): string
    {
        return '<svg class="i" viewBox="0 0 24 24" aria-hidden="true"><path pathLength="1" d="' . self::$icons[$name] . '"/></svg>';
    }

    /** Filled logo for the contact buttons: official WhatsApp / Facebook / Gmail (Simple Icons), phone (Material Icons). */
    public function brand(string $name): string
    {
        return '<svg class="i i--logo" viewBox="0 0 24 24" aria-hidden="true"><path d="' . self::BRANDS[$name] . '"/></svg>';
    }

    public function arrowLink(string $label, string $href): string
    {
        return '<a class="arrow-link" href="' . $href . '">' . $label . '<span class="circ">' . $this->ic('arrow') . '</span></a>';
    }

    /** Layout image under public/assets/img (logo, photos). */
    public function img(string $src, string $alt = '', string $cls = '', bool $lazy = true): string
    {
        return $this->picture('assets/img/' . $src, null, $alt, $cls, $lazy);
    }

    /** Image from the media library. */
    /** How wide images are shown, for srcset: cards and thumbnails (default), and the big ones beside text. */
    public const SIZES_CARD = '(max-width: 767px) 50vw, 400px';
    public const SIZES_WIDE = '(max-width: 1023px) 100vw, 640px';

    /**
     * Image from the media library. Uploads have several widths: the browser picks one through srcset/sizes.
     * Files from the old site that were never resized keep the single <img> + WebP of img().
     */
    public function media(?array $m, string $alt = '', string $cls = '', bool $lazy = true, string $sizes = self::SIZES_CARD): string
    {
        if ($m === null) {
            return '';
        }
        $variants = is_string($m['variants'] ?? null) ? json_decode($m['variants'], true) : ($m['variants'] ?? null);
        if (!$variants) {
            return $this->picture($m['path'], [(int) $m['width'], (int) $m['height']], $alt, $cls, $lazy);
        }
        $set = fn (string $key): string => implode(', ', array_map(fn ($v) => $this->root . $v[$key] . ' ' . $v['w'] . 'w', $variants));
        $sizesAttr = count($variants) > 1 ? ' sizes="' . $sizes . '"' : '';
        return sprintf('<picture><source srcset="%s"%s type="image/webp"><img src="%s"%s%s alt="%s" width="%d" height="%d"%s%s></picture>',
            $set('webp'), $sizesAttr, $this->root . $m['path'], count($variants) > 1 ? ' srcset="' . $set('src') . '"' : '', $sizesAttr,
            $this->e($alt), $m['width'], $m['height'], $cls !== '' ? ' class="' . $cls . '"' : '', $lazy ? ' loading="lazy"' : '');
    }

    /**
     * Hero background: a library picture, or a muted looping video with its poster. An old-site video has a .webm
     * next to the .mp4, offered first (smaller); the poster is its WebP when there is one, like picture().
     */
    public function heroMedia(array $slide): string
    {
        $m = $this->data->media((int) $slide['media_id']);
        if ($m['kind'] !== 'video') {
            return $this->media($m, '', 'hero__media', false, '100vw'); // background behind the text: no alt
        }
        $poster = '';
        if (($p = $this->data->media($slide['poster_id'] === null ? null : (int) $slide['poster_id'])) !== null) {
            $variants = is_string($p['variants'] ?? null) ? json_decode($p['variants'], true) : ($p['variants'] ?? null);
            $webp = $variants ? end($variants)['webp'] : preg_replace('/\.[a-z]+$/', '.webp', $p['path']);
            $poster = ' poster="' . $this->root . (is_file($this->publicDir . '/' . $webp) ? $webp : $p['path']) . '"';
        }
        $webm = preg_replace('/\.mp4$/', '.webm', $m['path']);
        $sources = (is_file($this->publicDir . '/' . $webm) ? '<source src="' . $this->root . $webm . '" type="video/webm">' : '')
            . '<source src="' . $this->root . $m['path'] . '" type="video/mp4">';
        return '<video class="hero__media"' . $poster . ' autoplay muted loop playsinline preload="metadata">' . $sources . '</video>';
    }

    /** <img> with its real size (no layout shift), in a <picture> with the WebP next to it when there is one. */
    private function picture(string $path, ?array $size, string $alt, string $cls, bool $lazy): string
    {
        $size ??= self::$imgSize[$path] ??= array_slice(getimagesize($this->publicDir . '/' . $path) ?: throw new \RuntimeException('Not an image: ' . $path), 0, 2);
        $tag = sprintf('<img src="%s" alt="%s" width="%d" height="%d"%s%s>', $this->root . $path, $this->e($alt), $size[0], $size[1],
            $cls !== '' ? ' class="' . $cls . '"' : '', $lazy ? ' loading="lazy"' : '');
        $webp = preg_replace('/\.[a-z]+$/', '.webp', $path);
        if (str_ends_with($path, '.svg') || !is_file($this->publicDir . '/' . $webp)) {
            return $tag;
        }
        return '<picture><source srcset="' . $this->root . $webp . '" type="image/webp">' . $tag . '</picture>';
    }

    /** Adds ?v=<content hash> to every asset URL, so a changed file is never served from a stale year-long cache. */
    public function versioned(string $doc): string
    {
        return (string) preg_replace_callback('#((?:\.\./)+(?:public/)?assets/)([^"?\#)\s]+)#', function (array $m): string {
            $v = self::$assetVersion[$m[2]] ??= substr(md5_file($this->publicDir . '/assets/' . $m[2]) ?: '', 0, 8);
            return $m[1] . $m[2] . '?v=' . $v;
        }, $doc);
    }

    // ------------------------------------------------------------ page meta, set by each page template
    /** A title or description entered in /admin/seo for this page (PageList::SEO) wins over the template's. */
    public function meta(string $slug, string $title, string $desc = '', bool $overlay = false, ?string $active = null): void
    {
        $seo = $this->data->seoPage($slug) ?? [];
        $own = $this->l->tr($seo, 'title') !== '';
        $title = $own ? $this->l->tr($seo, 'title') : $title;
        $desc = $this->l->tr($seo, 'desc') ?: $desc;
        $this->meta = compact('slug', 'title', 'desc', 'overlay', 'active', 'own');
    }

    /** @return array meta() values + ld (JSON-LD objects) */
    public function takeMeta(): array
    {
        [$meta, $ld, $this->meta, $this->ld] = [$this->meta, $this->ld, [], []];
        return $meta ? $meta + ['ld' => $ld] : throw new \LogicException('Page template did not call $v->meta()');
    }

    // ------------------------------------------------------------ full URLs and structured data (JSON-LD)
    /** Full URL of a page in this language (or $lang), or null without a base URL. */
    public function url(string $page, ?string $lang = null): ?string
    {
        return $this->baseUrl === null ? null : rtrim($this->baseUrl, '/') . '/' . ($lang ?? $this->lang) . '/' . $page . '.html';
    }

    /** Full URL of a file under public/ (assets/..., uploads/...), or null without a base URL. */
    public function fileUrl(?string $path): ?string
    {
        return $this->baseUrl === null || $path === null ? null : rtrim($this->baseUrl, '/') . '/' . $path;
    }

    /** Adds a schema.org object to the page head. Empty values (no base URL, no picture) are left out. */
    public function schema(array $thing): void
    {
        $this->ld[] = ['@context' => 'https://schema.org'] + self::withoutEmpty($thing);
    }

    private static function withoutEmpty(array $a): array
    {
        foreach ($a as $k => $value) {
            if (is_array($value)) {
                $a[$k] = $value = self::withoutEmpty($value);
            }
            if ($value === null || $value === '' || $value === []) {
                unset($a[$k]);
            }
        }
        return $a;
    }

    /** The company, for the home page and as author/publisher of news. */
    public function organization(): array
    {
        $a = $this->data->setting('address', []);
        return [
            '@type' => 'Organization',
            'name' => $this->t('co_name'),
            'alternateName' => 'XTK',
            'url' => $this->url('index'),
            'logo' => $this->fileUrl('assets/img/logo.png'),
            'description' => $this->t('co_tag'),
            'email' => $this->contact('email'),
            'telephone' => $this->contact('tel') !== '' ? $this->telHref($this->contact('tel')) : '',
            'foundingDate' => (string) ($this->data->setting('stats')['founded'] ?? ''),
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => ($a[$this->lang] ?? '') !== '' ? $a[$this->lang] : ($a['en'] ?? ''),
                'addressLocality' => 'Vientiane', 'addressCountry' => 'LA'],
        ];
    }

    // ------------------------------------------------------------ contact details (settings)
    public function contact(string $key): string
    {
        return (string) ($this->data->setting('contact')[$key] ?? '');
    }

    /** "(856-21) 563121" -> "+85621563121", "020 5589 2929" -> "+8562055892929" */
    public function telHref(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number);
        return '+' . (str_starts_with($digits, '856') ? $digits : '856' . ltrim($digits, '0'));
    }

    /** Turnstile site key for the contact form ('' = off: no keys in config.php, or a preview). */
    public function turnstileKey(): string
    {
        return $this->preview ? '' : Turnstile::siteKey();
    }

    public function whatsapp(): string
    {
        return 'https://api.whatsapp.com/send?phone=' . $this->contact('whatsapp');
    }

    public function address(): string
    {
        $a = $this->data->setting('address', []);
        return $this->e(($a[$this->lang] ?? '') !== '' ? $a[$this->lang] : ($a['en'] ?? ''));
    }

    public function mapLink(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query=' . $this->mapQuery();
    }

    public function mapEmbed(): string
    {
        return 'https://www.google.com/maps?q=' . $this->mapQuery() . '&output=embed';
    }

    /**
     * Escaped plain text from the admin with the contact details filled in, so they follow the settings:
     * {tel} {mobile} {email} {address}. A blank line starts a new paragraph inside the same <p>, as <br><br>.
     */
    public function withContact(string $text): string
    {
        return strtr(nl2br($this->e(trim($text)), false), [
            '{tel}' => $this->e($this->contact('tel')),
            '{mobile}' => $this->e($this->contact('mobile')),
            '{email}' => $this->e($this->contact('email')),
            '{address}' => $this->address(),
        ]);
    }

    private function mapQuery(): string
    {
        return str_replace(' ', '+', $this->e((string) ($this->data->setting('map')['query'] ?? '')));
    }

    // ------------------------------------------------------------ products and suppliers
    public function productPage(array $p): string
    {
        return 'product-' . $p['slug'];
    }

    /** Plain text from the admin as paragraphs: a blank line starts a new one, a single line break stays. */
    public function paragraphs(string $text, string $attrs = ''): string
    {
        $out = '';
        foreach (preg_split('/\n\s*\n/', trim($text)) ?: [] as $para) {
            $out .= '<p' . $attrs . '>' . nl2br($this->e(trim($para)), false) . '</p>';
        }
        return $out;
    }

    /** Like paragraphs(), and a block whose lines all start with "- " becomes a list (a leader's education, career). */
    public function story(string $text): string
    {
        $out = '';
        foreach (preg_split('/\n\s*\n/', trim($text)) ?: [] as $block) {
            $lines = array_map('trim', explode("\n", trim($block)));
            if (array_filter($lines, static fn ($l) => !str_starts_with($l, '- ')) === []) {
                $out .= '<ul>' . implode('', array_map(fn ($l) => '<li>' . $this->e(trim(substr($l, 2))) . '</li>', $lines)) . '</ul>';
            } else {
                $out .= '<p>' . nl2br($this->e(implode("\n", $lines)), false) . '</p>';
            }
        }
        return $out;
    }

    /** One sentence about the product; falls back to the group summary. */
    public function productText(array $p, array $group): string
    {
        return $this->l->tr($p, 'summary') !== '' ? $this->tr($p, 'summary') : $this->tr($group, 'summary');
    }

    public function country(array $s): string
    {
        return Countries::name($s['country_code'], $this->lang);
    }

    /** The logo at max height $h. Until the client sends the file, a text placeholder takes its place. */
    public function supplierLogo(array $s, int $h, bool $lazy = true): string
    {
        $logo = $this->data->media($s['logo_id'] === null ? null : (int) $s['logo_id']);
        if ($logo === null) {
            return '<span class="logo-ph" style="--logo-h:' . $h . 'px">' . $this->e($s['name']) . '</span>';
        }
        return sprintf('<img src="%s" alt="%s" width="%d" height="%d" style="--logo-h:%dpx"%s>', $this->root . $logo['path'], $this->e($s['name']),
            $logo['width'], $logo['height'], $h, $lazy ? ' loading="lazy"' : '');
    }

    public function supplierMeta(): string
    {
        $s = $this->data->suppliers();
        return $this->f('sup_meta', count($s), count(array_unique(array_column($s, 'country_code'))));
    }
}
