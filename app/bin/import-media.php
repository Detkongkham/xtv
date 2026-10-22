<?php
// Give the pictures that came with the old site (media rows pointing at public/assets/img/...) the same treatment
// as uploads: rebuilt by GD into uploads/ with 480/960/1600 widths, WebP + JPEG. Run once after seed; safe to rerun.
// The original files stay in public/assets/img/ (other pages still use some of them).
//   php bin/import-media.php
declare(strict_types=1);

use App\Core\App;
use App\Repository\MediaRepository;
use App\Service\MediaService;

require __DIR__ . '/../bootstrap.php';

$rows = (new MediaRepository(App::db()))->legacyImages();
$service = new MediaService(App::db(), dirname(App::path()) . '/public');
foreach ($rows as $m) {
    $service->importExisting($m);
    echo $m['path'], "\n";
}
printf("Imported %d file(s). Publish to use them on the site.\n", count($rows));
