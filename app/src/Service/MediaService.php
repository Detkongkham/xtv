<?php
declare(strict_types=1);

namespace App\Service;

use App\Core\Db;

/**
 * Stores files in public/uploads/YYYY/MM/ and registers them in `media` (docs/plan/03-backend.md §2.7).
 *
 * The type is read from the content (finfo), never from the name or the browser's claim. Every image is decoded
 * and written again by GD, which drops metadata and anything hidden in the file. Photos get up to 3 widths
 * (480/960/1600, never enlarged), each as WebP plus a JPEG fallback (PNG when the image has transparency).
 * `media.path` is the largest fallback; `media.variants` lists every width.
 */
final class MediaService
{
    public const WIDTHS = [480, 960, 1600];
    public const IMAGE_MAX_BYTES = 8 * 1024 * 1024;
    public const PDF_MAX_BYTES = 20 * 1024 * 1024;
    public const MAX_PIXELS = 40_000_000;  // a 40 MP photo needs ~160 MB in GD
    public const LOGO_MIN_WIDTH = 400;
    public const LOGO_MAX_WIDTH = 480;     // logos show at most 120px high; this keeps the files small
    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const LOGO_TYPES = ['image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct(private Db $db, private string $publicDir)
    {
    }

    /**
     * Any library upload: an image (JPG/PNG/WebP, ≤ 8 MB) or a PDF (≤ 20 MB).
     * @param array $file one entry of $_FILES
     * @return int media id
     * @throws \InvalidArgumentException with a message for the user
     */
    public function store(array $file, ?int $userId, bool $imagesOnly = false): int
    {
        $tmp = $this->checkUpload($file, self::PDF_MAX_BYTES);
        $mime = self::mime($tmp);
        $name = self::originalName($file);
        if ($mime === 'application/pdf' && !$imagesOnly) {
            return $this->writePdf($tmp, $name, $userId);
        }
        if (!in_array($mime, self::IMAGE_TYPES, true)) {
            throw new \InvalidArgumentException($name . ($imagesOnly ? ': ຮັບສະເພາະຮູບ JPG, PNG ຫຼື WebP.' : ': ຮັບສະເພາະຮູບ JPG, PNG, WebP ຫຼື ໄຟລ໌ PDF.'));
        }
        if (filesize($tmp) > self::IMAGE_MAX_BYTES) {
            throw new \InvalidArgumentException($name . ': ຮູບໃຫຍ່ເກີນ ' . (self::IMAGE_MAX_BYTES >> 20) . ' MB.');
        }
        return $this->storeImage($this->decode($tmp, $mime, $name), $name, $userId);
    }

    /**
     * A document for the downloads page: PDF only, ≤ 20 MB.
     * @throws \InvalidArgumentException with a message for the user
     */
    public function storePdf(array $file, ?int $userId): int
    {
        $tmp = $this->checkUpload($file, self::PDF_MAX_BYTES);
        $name = self::originalName($file);
        if (self::mime($tmp) !== 'application/pdf') {
            throw new \InvalidArgumentException($name . ': ຮັບສະເພາະໄຟລ໌ PDF.');
        }
        return $this->writePdf($tmp, $name, $userId);
    }

    /**
     * A file that came with the old site (public/assets/img/...): gets the same sizes as an upload. Updates the row.
     */
    public function importExisting(array $media): void
    {
        $full = $this->publicDir . '/' . $media['path'];
        $mime = self::mime($full);
        $img = $this->decode($full, $mime, $media['original_name']);
        [$path, $variants, $w, $h, $fmime] = $this->writeVariants($img);
        $this->db->run('UPDATE media SET path = ?, mime = ?, size_bytes = ?, width = ?, height = ?, variants = ? WHERE id = ?',
            [$path, $fmime, (int) filesize($this->publicDir . '/' . $path), $w, $h, json_encode($variants, JSON_UNESCAPED_SLASHES), (int) $media['id']]);
    }

    /**
     * A supplier logo: PNG or WebP (transparency kept), at least 400px wide. Wider ones are scaled down to 480px.
     * @throws \InvalidArgumentException with a message for the user
     */
    public function storeLogo(array $file, ?int $userId): int
    {
        $tmp = $this->checkUpload($file, self::IMAGE_MAX_BYTES);
        $mime = self::mime($tmp);
        if (!isset(self::LOGO_TYPES[$mime])) {
            throw new \InvalidArgumentException('ໂລໂກ້ຕ້ອງເປັນ PNG ຫຼື WebP.');
        }
        $img = $this->decode($tmp, $mime, 'logo');
        $w = imagesx($img);
        if ($w < self::LOGO_MIN_WIDTH) {
            throw new \InvalidArgumentException('ໂລໂກ້ຕ້ອງກວ້າງຢ່າງໜ້ອຍ ' . self::LOGO_MIN_WIDTH . ' px (ອັນນີ້ ' . $w . ' px).');
        }
        $img = self::fitWidth($img, self::LOGO_MAX_WIDTH);
        $ext = self::LOGO_TYPES[$mime];
        $path = $this->newBase() . '.' . $ext;
        $this->write($img, $path, $ext);
        return $this->db->insert('media', [
            'kind' => 'image', 'path' => $path, 'original_name' => self::originalName($file), 'mime' => $mime,
            'size_bytes' => (int) filesize($this->publicDir . '/' . $path), 'width' => imagesx($img), 'height' => imagesy($img), 'created_by' => $userId,
        ]);
    }

    /** Deletes the files of a media row (the row itself is deleted by the repository). Old-site files are kept. */
    public function deleteFiles(array $media): void
    {
        $paths = [$media['path']];
        foreach (json_decode((string) ($media['variants'] ?? 'null'), true) ?: [] as $v) {
            array_push($paths, $v['src'], $v['webp']);
        }
        foreach (array_unique($paths) as $p) {
            if (str_starts_with($p, 'uploads/') && !str_contains($p, '..') && is_file($this->publicDir . '/' . $p)) {
                unlink($this->publicDir . '/' . $p);
            }
        }
    }

    /** Scales down to $max px wide (never up), keeping transparency. (imagescale() returns false on some GD builds.) */
    public static function fitWidth(\GdImage $img, int $max): \GdImage
    {
        if (imagesx($img) > $max) {
            $out = self::resized($img, $max);
            imagedestroy($img);
            $img = $out;
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);
        return $img;
    }

    /** The widths to make for an image $w px wide: the standard ones below it, plus its own width up to 1600. */
    public static function widthsFor(int $w): array
    {
        $out = array_filter(self::WIDTHS, static fn ($x) => $x < $w);
        $out[] = min($w, max(self::WIDTHS));
        return array_values(array_unique($out));
    }

    // ------------------------------------------------------------------ internals
    private function storeImage(\GdImage $img, string $name, ?int $userId): int
    {
        [$path, $variants, $w, $h, $mime] = $this->writeVariants($img);
        return $this->db->insert('media', [
            'kind' => 'image', 'path' => $path, 'original_name' => $name, 'mime' => $mime,
            'size_bytes' => (int) filesize($this->publicDir . '/' . $path), 'width' => $w, 'height' => $h,
            'variants' => json_encode($variants, JSON_UNESCAPED_SLASHES), 'created_by' => $userId,
        ]);
    }

    /** @return array{0: string, 1: list<array{w: int, h: int, webp: string, src: string}>, 2: int, 3: int, 4: string} */
    private function writeVariants(\GdImage $img): array
    {
        $alpha = self::hasTransparency($img);
        $ext = $alpha ? 'png' : 'jpg';
        $base = $this->newBase();
        $variants = [];
        foreach (self::widthsFor(imagesx($img)) as $w) {
            $v = $w === imagesx($img) ? $img : self::resized($img, $w);
            $item = ['w' => imagesx($v), 'h' => imagesy($v), 'webp' => "{$base}-{$w}.webp", 'src' => "{$base}-{$w}.{$ext}"];
            $this->write($v, $item['webp'], 'webp');
            $this->write($v, $item['src'], $ext);
            $variants[] = $item;
            if ($v !== $img) {
                imagedestroy($v);
            }
        }
        $largest = end($variants);
        imagedestroy($img);
        return [$largest['src'], $variants, $largest['w'], $largest['h'], $alpha ? 'image/png' : 'image/jpeg'];
    }

    private function writePdf(string $tmp, string $name, ?int $userId): int
    {
        // a PDF cannot be rebuilt like an image; it is only ever served as a download, never run
        $path = $this->newBase() . '.pdf';
        if (!copy($tmp, $this->publicDir . '/' . $path)) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return $this->db->insert('media', [
            'kind' => 'pdf', 'path' => $path, 'original_name' => $name, 'mime' => 'application/pdf',
            'size_bytes' => (int) filesize($tmp), 'created_by' => $userId,
        ]);
    }

    private function decode(string $file, string $mime, string $name): \GdImage
    {
        $info = @getimagesize($file);
        if ($info === false || $info[0] * $info[1] > self::MAX_PIXELS) {
            throw new \InvalidArgumentException($name . ($info === false ? ': ອ່ານຮູບນີ້ບໍ່ໄດ້.' : ': ຮູບໃຫຍ່ເກີນໄປ (ສູງສຸດ 40 ລ້ານພິກເຊລ).'));
        }
        @ini_set('memory_limit', '512M');
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/png' => @imagecreatefrompng($file),
            'image/webp' => @imagecreatefromwebp($file),
            default => false,
        };
        if ($img === false) {
            throw new \InvalidArgumentException($name . ': ອ່ານຮູບນີ້ບໍ່ໄດ້. ກະລຸນາບັນທຶກເປັນ JPG ໃໝ່ ແລ້ວອັບອີກຄັ້ງ.');
        }
        if (!imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }
        // phone photos are stored sideways with an EXIF note; turn them upright since the note is dropped
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $angle = [3 => 180, 6 => 270, 8 => 90][(int) (@exif_read_data($file)['Orientation'] ?? 1)] ?? 0;
            if ($angle) {
                $img = imagerotate($img, $angle, 0) ?: $img;
            }
        }
        return $img;
    }

    private static function resized(\GdImage $img, int $width): \GdImage
    {
        [$w, $h] = [imagesx($img), imagesy($img)];
        $nh = max(1, (int) round($h * $width / $w));
        $out = imagecreatetruecolor($width, $nh);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagecopyresampled($out, $img, 0, 0, 0, 0, $width, $nh, $w, $h);
        return $out;
    }

    /** Looks at a small copy: any pixel that is not fully opaque. */
    private static function hasTransparency(\GdImage $img): bool
    {
        $s = self::resized($img, min(64, imagesx($img)));
        for ($x = 0, $w = imagesx($s), $h = imagesy($s); $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                if ((imagecolorat($s, $x, $y) >> 24) & 0x7F) {
                    imagedestroy($s);
                    return true;
                }
            }
        }
        imagedestroy($s);
        return false;
    }

    private function write(\GdImage $img, string $path, string $ext): void
    {
        $full = $this->publicDir . '/' . $path;
        if ($ext === 'jpg') {
            imageinterlace($img, true); // progressive
        }
        imagesavealpha($img, $ext !== 'jpg');
        $ok = match ($ext) {
            'jpg' => imagejpeg($img, $full, 82),
            'png' => imagepng($img, $full, 9),
            'webp' => imagewebp($img, $full, 80),
        };
        if (!$ok) {
            throw new \RuntimeException('Cannot write ' . $full);
        }
    }

    /** @return string temp path of a correctly uploaded file */
    private function checkUpload(array $file, int $maxBytes): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $name = self::originalName($file);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || (int) ($file['size'] ?? 0) > $maxBytes) {
            throw new \InvalidArgumentException($name . ': ໄຟລ໌ໃຫຍ່ເກີນໄປ.');
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new \InvalidArgumentException($name . ': ອັບໄຟລ໌ບໍ່ສຳເລັດ. ກະລຸນາລອງໃໝ່.');
        }
        return (string) $file['tmp_name'];
    }

    private static function mime(string $file): string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($file) ?: '';
    }

    private static function originalName(array $file): string
    {
        return mb_substr(basename(str_replace('\\', '/', (string) ($file['name'] ?? 'file'))), 0, 255) ?: 'file';
    }

    /** uploads/2026/10/ab12cd34ef56 — random, so names never collide or reveal the original name */
    private function newBase(): string
    {
        $dir = 'uploads/' . date('Y/m');
        if (!is_dir($this->publicDir . '/' . $dir) && !mkdir($this->publicDir . '/' . $dir, 0755, true)) {
            throw new \RuntimeException('Cannot create ' . $dir);
        }
        return $dir . '/' . bin2hex(random_bytes(6));
    }
}
