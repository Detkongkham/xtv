<?php
declare(strict_types=1);

namespace App\Service;

use App\Core\Db;

/**
 * Stores uploaded files in public/uploads/YYYY/MM/ and registers them in `media`.
 * The type is read from the file's content (finfo), never from its name or the browser's claim.
 * Only supplier logos for now (P2-C12); the full library with sizes and WebP comes in P2-C5.
 */
final class MediaService
{
    public const LOGO_MIN_WIDTH = 400;
    public const LOGO_MAX_WIDTH = 480;    // logos show at most 120px high; this keeps the files small
    public const MAX_BYTES = 5 * 1024 * 1024;
    private const LOGO_TYPES = ['image/png' => 'png', 'image/webp' => 'webp'];

    public function __construct(private Db $db, private string $publicDir)
    {
    }

    /**
     * A supplier logo: PNG or WebP (transparency kept), at least 400px wide. Wider ones are scaled down to 480px.
     * @param array $file one entry of $_FILES
     * @return int media id
     * @throws \InvalidArgumentException with a message for the user
     */
    public function storeLogo(array $file, ?int $userId): int
    {
        $tmp = $this->checkUpload($file);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
        if (!isset(self::LOGO_TYPES[$mime])) {
            throw new \InvalidArgumentException('ໂລໂກ້ຕ້ອງເປັນ PNG ຫຼື WebP.');
        }
        $img = $mime === 'image/png' ? @imagecreatefrompng($tmp) : @imagecreatefromwebp($tmp);
        if ($img === false) {
            throw new \InvalidArgumentException('ອ່ານຮູບນີ້ບໍ່ໄດ້. ກະລຸນາບັນທຶກເປັນ PNG ໃໝ່ ແລ້ວອັບອີກຄັ້ງ.');
        }
        $w = imagesx($img);
        if ($w < self::LOGO_MIN_WIDTH) {
            throw new \InvalidArgumentException('ໂລໂກ້ຕ້ອງກວ້າງຢ່າງໜ້ອຍ ' . self::LOGO_MIN_WIDTH . ' px (ອັນນີ້ ' . $w . ' px).');
        }
        $img = self::fitWidth($img, self::LOGO_MAX_WIDTH);

        $ext = self::LOGO_TYPES[$mime];
        $path = $this->newPath($ext);
        $full = $this->publicDir . '/' . $path;
        $ok = $ext === 'png' ? imagepng($img, $full, 9) : imagewebp($img, $full, 90);
        if (!$ok) {
            throw new \RuntimeException('Cannot write ' . $full);
        }
        $id = $this->db->insert('media', [
            'kind' => 'image', 'path' => $path, 'original_name' => mb_substr(basename((string) ($file['name'] ?? 'logo')), 0, 255),
            'mime' => $mime, 'size_bytes' => (int) filesize($full), 'width' => imagesx($img), 'height' => imagesy($img), 'created_by' => $userId,
        ]);
        imagedestroy($img);
        return $id;
    }

    /** Scales down to $max px wide (never up), keeping transparency. (imagescale() returns false on some GD builds.) */
    public static function fitWidth(\GdImage $img, int $max): \GdImage
    {
        [$w, $h] = [imagesx($img), imagesy($img)];
        if ($w > $max) {
            $nh = max(1, (int) round($h * $max / $w));
            $out = imagecreatetruecolor($max, $nh);
            imagealphablending($out, false);
            imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
            imagecopyresampled($out, $img, 0, 0, 0, 0, $max, $nh, $w, $h);
            imagedestroy($img);
            $img = $out;
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);
        return $img;
    }

    /** @return string temp path of a correctly uploaded file */
    private function checkUpload(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || (int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new \InvalidArgumentException('ໄຟລ໌ໃຫຍ່ເກີນໄປ (ສູງສຸດ ' . (self::MAX_BYTES >> 20) . ' MB).');
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new \InvalidArgumentException('ອັບໄຟລ໌ບໍ່ສຳເລັດ. ກະລຸນາລອງໃໝ່.');
        }
        return (string) $file['tmp_name'];
    }

    /** uploads/2026/10/ab12cd34ef56.png — a random name, so it never collides or reveals the original name */
    private function newPath(string $ext): string
    {
        $dir = 'uploads/' . date('Y/m');
        if (!is_dir($this->publicDir . '/' . $dir) && !mkdir($this->publicDir . '/' . $dir, 0755, true)) {
            throw new \RuntimeException('Cannot create ' . $dir);
        }
        return $dir . '/' . bin2hex(random_bytes(6)) . '.' . $ext;
    }
}
