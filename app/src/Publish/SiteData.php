<?php
declare(strict_types=1);

namespace App\Publish;

use App\Core\Db;

/**
 * Everything the public pages show, loaded in one go (a few hundred rows). Only visible / published rows are kept.
 * Rows are plain arrays as they come from the tables; links are resolved through the id maps below.
 */
final class SiteData
{
    /** @var array<int, array> keyed by id */
    private array $media = [];
    private array $divisions = [];
    private array $groupsOf = [];
    private array $productsOf = [];
    private array $products = [];
    private array $specs = [];
    private array $suppliers = [];
    private array $suppliersOf = [];
    private array $productsOfSupplier = [];
    private array $news = [];
    /** draft news by slug, for the admin preview only */
    private array $draftNews = [];
    private array $settings = [];
    private array $redirects = [];
    private array $downloads = [];
    private array $faqs = [];
    private array $heroSlides = [];
    private array $seoPages = [];

    private function __construct(array $t)
    {
        foreach ($t['media'] as $m) {
            $this->media[(int) $m['id']] = $m;
        }
        $visible = static fn (array $rows): array => array_values(array_filter($rows, static fn ($r) => (int) ($r['is_visible'] ?? 1) === 1));
        $sorted = static function (array $rows, string $key = 'sort'): array {
            usort($rows, static fn ($a, $b) => [(int) $a[$key], (int) $a['id']] <=> [(int) $b[$key], (int) $b['id']]);
            return $rows;
        };

        foreach ($sorted($visible($t['divisions'])) as $d) {
            $this->divisions[(int) $d['id']] = $d;
        }
        foreach ($sorted($visible($t['product_groups'])) as $g) {
            if (isset($this->divisions[(int) $g['division_id']])) {
                $this->groupsOf[(int) $g['division_id']][] = $g;
            }
        }
        $groupIds = [];
        foreach ($this->groupsOf as $groups) {
            foreach ($groups as $g) {
                $groupIds[(int) $g['id']] = true;
            }
        }
        foreach ($sorted($visible($t['products'])) as $p) {
            if (isset($groupIds[(int) $p['group_id']])) {
                $this->productsOf[(int) $p['group_id']][] = $p;
            }
        }
        // products in page order: division, group, product
        foreach ($this->divisions as $d) {
            foreach ($this->groupsOf[(int) $d['id']] ?? [] as $g) {
                foreach ($this->productsOf[(int) $g['id']] ?? [] as $p) {
                    $this->products[(int) $p['id']] = ['division' => $d, 'group' => $g, 'product' => $p];
                }
            }
        }

        foreach ($sorted($t['product_specs'] ?? []) as $row) {
            $this->specs[(int) $row['product_id']][] = $row;
        }

        foreach ($sorted($visible($t['suppliers'])) as $s) {
            $this->suppliers[(int) $s['id']] = $s;
        }
        $links = $t['product_suppliers'];
        usort($links, static fn ($a, $b) => [(int) $a['product_id'], (int) $a['sort']] <=> [(int) $b['product_id'], (int) $b['sort']]);
        foreach ($links as $l) {
            if (isset($this->suppliers[(int) $l['supplier_id']], $this->products[(int) $l['product_id']])) {
                $this->suppliersOf[(int) $l['product_id']][] = $this->suppliers[(int) $l['supplier_id']];
                $this->productsOfSupplier[(int) $l['supplier_id']][(int) $l['product_id']] = true;
            }
        }

        $news = array_values(array_filter($t['news'], static fn ($n) => $n['status'] === 'published'));
        usort($news, static fn ($a, $b) => [$b['published_on'], (int) $b['id']] <=> [$a['published_on'], (int) $a['id']]);
        $this->news = $news;
        foreach ($t['news'] as $n) {
            if ($n['status'] === 'draft') {
                $this->draftNews[$n['slug']] = $n;
            }
        }
        $this->settings = $t['settings'];
        $this->redirects = $t['redirects'] ?? [];
        foreach ($sorted($visible($t['downloads'] ?? [])) as $dl) {
            // a division that is hidden takes its catalogue with it
            if (isset($this->media[(int) $dl['file_id']]) && ($dl['division_id'] === null || isset($this->divisions[(int) $dl['division_id']]))) {
                $this->downloads[] = $dl;
            }
        }
        $this->faqs = $sorted($visible($t['faqs'] ?? []));
        foreach ($sorted($visible($t['hero_slides'] ?? [])) as $h) {
            if (isset($this->media[(int) $h['media_id']])) {
                $this->heroSlides[] = $h;
            }
        }
        foreach ($t['seo_pages'] ?? [] as $row) {
            $this->seoPages[$row['page']] = $row;
        }
    }

    public static function fromDb(Db $db): self
    {
        $tables = [];
        foreach (['media', 'divisions', 'product_groups', 'products', 'product_specs', 'suppliers', 'product_suppliers', 'news', 'redirects', 'downloads', 'faqs', 'hero_slides', 'seo_pages'] as $table) {
            $tables[$table] = $db->all('SELECT * FROM `' . $table . '`');
        }
        $tables['settings'] = [];
        foreach ($db->all('SELECT `key`, value FROM settings') as $row) {
            $tables['settings'][$row['key']] = json_decode((string) $row['value'], true, 512, JSON_THROW_ON_ERROR);
        }
        return new self($tables);
    }

    /** From a seed file (database/seed/seed.json format), without a database. Used to check templates. */
    public static function fromSeedFile(string $file): self
    {
        $t = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $t['settings'] += ['noindex' => true];
        return new self($t);
    }

    public function media(?int $id): ?array
    {
        return $id === null ? null : ($this->media[$id] ?? null);
    }

    /** @return list<array> */
    public function divisions(): array
    {
        return array_values($this->divisions);
    }

    public function division(string $slug): ?array
    {
        foreach ($this->divisions as $d) {
            if ($d['slug'] === $slug) {
                return $d;
            }
        }
        return null;
    }

    public function groups(array $division): array
    {
        return $this->groupsOf[(int) $division['id']] ?? [];
    }

    public function products(array $group): array
    {
        return $this->productsOf[(int) $group['id']] ?? [];
    }

    /** @return list<array{division: array, group: array, product: array}> every visible product, in page order */
    public function allProducts(): array
    {
        return array_values($this->products);
    }

    /** @return array{division: array, group: array, product: array} */
    public function placeOf(array $product): array
    {
        return $this->products[(int) $product['id']];
    }

    public function divisionProducts(array $division): array
    {
        $out = [];
        foreach ($this->groups($division) as $g) {
            array_push($out, ...$this->products($g));
        }
        return $out;
    }

    public function group(string $code): ?array
    {
        foreach ($this->groupsOf as $groups) {
            foreach ($groups as $g) {
                if ($g['code'] === $code) {
                    return $g;
                }
            }
        }
        return null;
    }

    /** Specification rows entered in the admin (none = the template shows the general rows). */
    public function specs(array $product): array
    {
        return $this->specs[(int) $product['id']] ?? [];
    }

    public function featuredProducts(): array
    {
        $rows = array_filter(array_column($this->products, 'product'), static fn ($p) => (int) $p['is_featured'] === 1 && $p['image_id'] !== null);
        usort($rows, static fn ($a, $b) => [(int) $a['featured_sort'], (int) $a['id']] <=> [(int) $b['featured_sort'], (int) $b['id']]);
        return $rows;
    }

    /** @return list<array> */
    public function suppliers(): array
    {
        return array_values($this->suppliers);
    }

    public function featuredSuppliers(): array
    {
        $rows = array_values(array_filter($this->suppliers, static fn ($s) => (int) $s['is_featured'] === 1));
        usort($rows, static fn ($a, $b) => [(int) $a['featured_sort'], (int) $a['id']] <=> [(int) $b['featured_sort'], (int) $b['id']]);
        return $rows;
    }

    public function suppliersOf(array $product): array
    {
        return $this->suppliersOf[(int) $product['id']] ?? [];
    }

    /** @return list<array{division: array, group: array, product: array}> in page order */
    public function supplierProducts(array $supplier): array
    {
        $ids = $this->productsOfSupplier[(int) $supplier['id']] ?? [];
        return array_values(array_filter($this->products, static fn ($place) => isset($ids[(int) $place['product']['id']])));
    }

    /** A supplier's divisions come from its products, so they cannot disagree. */
    public function supplierDivisions(array $supplier): array
    {
        $ids = array_flip(array_map(static fn ($place) => (int) $place['division']['id'], $this->supplierProducts($supplier)));
        return array_values(array_filter($this->divisions, static fn ($d) => isset($ids[(int) $d['id']])));
    }

    public function news(): array
    {
        return $this->news;
    }

    /** A draft news item, which no published page shows (admin preview). */
    public function draftNews(string $slug): ?array
    {
        return $this->draftNews[$slug] ?? null;
    }

    /** News with a body get their own page (news-<slug>.html); the others only link to link_url. */
    public function hasPage(array $news): bool
    {
        return trim((string) $news['body_en']) !== '';
    }

    /** @return list<array{from_page: string, to_page: string}> old page names (no .html) and where they moved */
    public function redirects(): array
    {
        return $this->redirects;
    }

    /** Visible downloads with a file, in display order. */
    public function downloads(): array
    {
        return $this->downloads;
    }

    /** @return list<array> visible questions in order */
    public function faqs(): array
    {
        return $this->faqs;
    }

    /** @return list<array> visible hero slides in order, each with its picture or video in the library */
    public function heroSlides(): array
    {
        return $this->heroSlides;
    }

    /** Title/description entered for a fixed page in /admin/seo (columns title_<lang>, desc_<lang>), if any. */
    public function seoPage(string $page): ?array
    {
        return $this->seoPages[$page] ?? null;
    }

    public function divisionById(?int $id): ?array
    {
        return $id === null ? null : ($this->divisions[$id] ?? null);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}
