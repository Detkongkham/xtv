<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\App;
use App\Core\Countries;
use App\Core\Lang;
use App\Core\View;

/**
 * What a site template sees as $v: one language, the site data and small HTML helpers.
 * Templates live in templates/site/; they are written on several lines for reading, and partial() removes the
 * line breaks between tags again, so the pages come out as compact as the ones build.py made.
 */
final class SiteView
{
    /** Solutions: text key sol_<id>, picture, recommended product groups, division to link to. */
    public const SOLUTIONS = [
        ['hospital', 'photo/4779.jpg', ['g_imaging', 'g_surgical', 'g_disposable'], 'healthcare'],
        ['clinic', 'photo/35963.jpg', ['g_diagnostic', 'g_imaging'], 'healthcare'],
        ['lab', 'photo/4719.jpg', ['g_analyzers', 'g_reagents'], 'laboratory'],
        ['pharmacy', 'products/pharma.jpg', ['g_classes', 'g_diagnostic'], 'pharmaceuticals'],
        ['security', 'products/s01.jpg', ['g_xray', 'g_walk', 'g_hand', 'g_trace'], 'security'],
    ];
    public const SERVICES = [['srv1', 'tool'], ['srv2', 'shield'], ['srv3', 'cap'], ['srv4', 'box']];
    public const WHY = [['why1', 'award'], ['why2', 'users'], ['why3', 'tool'], ['why4', 'box']];

    private static array $icons;
    private static array $imgSize = [];
    private static array $assetVersion = [];

    public readonly string $lang;
    /** prefix that turns a path under public/ into a link from public/<lang>/ */
    private string $root;
    private array $meta = [];

    public function __construct(
        private Lang $l,
        public readonly SiteData $data,
        private string $publicDir,
        public readonly string $a = '../assets',
        public readonly bool $preview = false,
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
    public function meta(string $slug, string $title, string $desc = '', bool $overlay = false, ?string $active = null): void
    {
        $this->meta = compact('slug', 'title', 'desc', 'overlay', 'active');
    }

    public function takeMeta(): array
    {
        [$meta, $this->meta] = [$this->meta, []];
        return $meta ?: throw new \LogicException('Page template did not call $v->meta()');
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
