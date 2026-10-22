<?php
// Render the static site into a folder, without touching the live public/ pages (that is the Publisher's job, P2-C4).
//   php bin/build-site.php --out=DIR                       from the database
//   php bin/build-site.php --out=DIR --seed=FILE           from a seed.json, no database needed
//   php bin/build-site.php --out=DIR --seed=FILE --preview sample-data preview (noindex + preview bar)
//   --assets=PATH  link to assets/ from DIR/<lang>/ (default ../assets, i.e. DIR sits next to public/assets)
declare(strict_types=1);

use App\Core\App;
use App\Publish\SiteBuilder;
use App\Publish\SiteData;

require __DIR__ . '/../bootstrap.php';

$opt = getopt('', ['out:', 'seed:', 'assets:', 'preview']);
if (empty($opt['out'])) {
    fwrite(STDERR, "Usage: php bin/build-site.php --out=DIR [--seed=FILE] [--assets=PATH] [--preview]\n");
    exit(2);
}
$start = microtime(true);
$data = isset($opt['seed']) ? SiteData::fromSeedFile($opt['seed']) : SiteData::fromDb(App::db());
$builder = new SiteBuilder($data, dirname(App::path()) . '/public', $opt['assets'] ?? '../assets', isset($opt['preview']), App::config('base_url'));
$n = $builder->build(rtrim($opt['out'], '/'));
printf("pages written: %d | %.2f s | out: %s\n", $n, microtime(true) - $start, $opt['out']);
