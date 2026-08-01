<?php
declare(strict_types=1);

namespace App\Core;

class Uploader
{
    public const IMAGES = ['jpg', 'jpeg', 'png', 'webp'];
    public const PROOFS = ['jpg', 'jpeg', 'png', 'pdf'];
    public const ARTWORK = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'cdr', 'ai', 'psd', 'zip'];
    public const AUDIO = ['webm', 'ogg', 'mp3', 'm4a', 'wav'];

    private const MIME_MAP = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
        'webp' => ['image/webp'], 'pdf' => ['application/pdf'],
        'cdr' => ['application/octet-stream', 'application/x-cdr', 'application/coreldraw', 'image/x-coreldraw'],
        'ai' => ['application/postscript', 'application/pdf', 'application/illustrator'],
        'psd' => ['image/vnd.adobe.photoshop', 'application/octet-stream'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        'webm' => ['video/webm', 'audio/webm'], 'ogg' => ['audio/ogg', 'application/ogg', 'video/ogg'],
        'mp3' => ['audio/mpeg'], 'm4a' => ['audio/mp4', 'audio/x-m4a', 'video/mp4'], 'wav' => ['audio/wav', 'audio/x-wav'],
        'ico' => ['image/vnd.microsoft.icon', 'image/x-icon'],
    ];

    /**
     * Validate + store an uploaded file under uploads/{subdir}.
     * @return array{ok:bool,error?:string,path?:string,original?:string,mime?:string,size?:int}
     */
    public static function store(array $file, string $subdir, array $allowedExts, ?int $maxMb = null): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['ok' => false, 'error' => 'Invalid upload.'];
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'error' => 'No file uploaded.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed (code ' . $file['error'] . ').'];
        }

        $maxMb = $maxMb ?? Settings::getInt('upload_max_mb', 15);
        if ((int)$file['size'] > $maxMb * 1024 * 1024) {
            return ['ok' => false, 'error' => "File exceeds the {$maxMb} MB limit."];
        }

        $original = (string)($file['name'] ?? 'file');
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return ['ok' => false, 'error' => 'File type .' . $ext . ' is not allowed.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        $allowedMimes = self::MIME_MAP[$ext] ?? [];
        if ($allowedMimes && !in_array($mime, $allowedMimes, true)) {
            return ['ok' => false, 'error' => 'File content does not match its extension.'];
        }
        // Never allow anything PHP-ish regardless of claimed type
        if (preg_match('/php|phtml|phar/i', $ext) || str_contains($mime, 'php')) {
            return ['ok' => false, 'error' => 'File type not allowed.'];
        }

        $subdir = trim(preg_replace('/[^a-z0-9_\/\-]/i', '', $subdir) ?? '', '/');
        $dir = BASE_PATH . '/uploads/' . $subdir;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return ['ok' => false, 'error' => 'Upload directory is not writable.'];
        }

        $name = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $dest)
            : @rename($file['tmp_name'], $dest);
        if (!$moved) {
            return ['ok' => false, 'error' => 'Could not save the file.'];
        }
        @chmod($dest, 0644);

        return [
            'ok' => true,
            'path' => $subdir . '/' . $name,
            'original' => substr($original, 0, 255),
            'mime' => $mime,
            'size' => (int)$file['size'],
        ];
    }

    /** Create a thumbnail (max 400px) for an image; returns relative path or null. */
    public static function thumbnail(string $relativePath, int $maxSide = 400): ?string
    {
        $src = BASE_PATH . '/uploads/' . $relativePath;
        if (!is_file($src) || !function_exists('imagecreatetruecolor')) {
            return null;
        }
        $info = @getimagesize($src);
        if (!$info) {
            return null;
        }
        [$w, $h] = $info;
        $scale = min(1, $maxSide / max($w, $h));
        $tw = max(1, (int)($w * $scale));
        $th = max(1, (int)($h * $scale));
        $img = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            default => null,
        };
        if (!$img) {
            return null;
        }
        $thumb = imagecreatetruecolor($tw, $th);
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
        $rel = preg_replace('/(\.[a-z0-9]+)$/i', '_thumb.jpg', $relativePath);
        imagejpeg($thumb, BASE_PATH . '/uploads/' . $rel, 82);
        imagedestroy($img);
        imagedestroy($thumb);
        return $rel;
    }

    /** Diagonal business-name watermark on an image proof; returns relative path or null. */
    public static function watermark(string $relativePath, string $text): ?string
    {
        $src = BASE_PATH . '/uploads/' . $relativePath;
        if (!is_file($src) || !function_exists('imagecreatetruecolor')) {
            return null;
        }
        $info = @getimagesize($src);
        if (!$info) {
            return null; // PDFs etc. — serve as-is with overlay handled client-side
        }
        [$w, $h] = $info;
        $img = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            default => null,
        };
        if (!$img) {
            return null;
        }
        $color = imagecolorallocatealpha($img, 120, 120, 120, 85);
        $text = $text !== '' ? $text : 'PROOF';
        $step = max(160, (int)($w / 4));
        $fontSize = 5;
        $textW = imagefontwidth($fontSize) * strlen($text);
        for ($y = -$h; $y < $h * 2; $y += $step) {
            for ($x = -$w; $x < $w * 2; $x += ($textW + $step)) {
                imagestring($img, $fontSize, $x, $y, $text . '  •  PROOF', $color);
            }
        }
        $rel = preg_replace('/(\.[a-z0-9]+)$/i', '_wm.jpg', $relativePath);
        imagejpeg($img, BASE_PATH . '/uploads/' . $rel, 85);
        imagedestroy($img);
        return $rel;
    }
}
