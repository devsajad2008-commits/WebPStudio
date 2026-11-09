<?php

namespace App\Services\Shared;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ImageService
{
    private const MAX_IMAGE_PIXELS = 40_000_000;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(
            new Driver
        );
    }

    /**
     * Store an uploaded image after resizing and converting it to WebP.
     *
     * @return array{path: string, url: string, filename: string, disk: string, width: int, height: int, size: int, size_kb: float, mime: string}
     */
    public function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 800,
        int $maxHeight = 800,
        int $quality = 85,
        string $disk = 'public'
    ): array {
        if ($maxWidth < 1 || $maxHeight < 1 || $quality < 1 || $quality > 100) {
            throw new InvalidArgumentException('Image dimensions and quality must be within valid ranges.');
        }

        if (! $file->isValid()) {
            throw new RuntimeException(
                'فایل تصویر معتبر نیست.'
            );
        }

        $realMime = $file->getMimeType();

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/bmp',
            'image/tiff',
            'image/avif',
        ];

        if (
            ! $realMime ||
            ! in_array($realMime, $allowedMimes, true)
        ) {
            throw new RuntimeException(
                'نوع واقعی فایل تصویر پشتیبانی نمی‌شود. MIME: '
                .($realMime ?: 'unknown')
            );
        }

        $realPath = $file->getRealPath();

        if (! $realPath || ! is_readable($realPath)) {
            throw new RuntimeException(
                'مسیر فایل تصویر قابل خواندن نیست.'
            );
        }

        try {
            $imageSize = getimagesize($realPath);
        } catch (Throwable $exception) {
            throw new RuntimeException('فایل انتخاب‌شده تصویر معتبری نیست.', previous: $exception);
        }

        if ($imageSize === false || $imageSize[0] < 1 || $imageSize[1] < 1) {
            throw new RuntimeException('فایل انتخاب‌شده تصویر معتبری نیست.');
        }

        if ($imageSize[0] > intdiv(self::MAX_IMAGE_PIXELS, $imageSize[1])) {
            throw new RuntimeException('ابعاد تصویر بیشتر از حد مجاز است.');
        }

        $binary = file_get_contents($realPath);

        if ($binary === false || $binary === '') {
            throw new RuntimeException(
                'محتوای فایل تصویر قابل خواندن نیست.'
            );
        }

        $directory = $this->normalizeStoragePath($directory)
            ?? throw new InvalidArgumentException('The storage directory must be a non-empty relative path.');

        $storage = Storage::disk($disk);
        $filename = $this->uniqueFilename($directory, $storage);
        $path = "{$directory}/{$filename}";

        try {
            $image = $this->manager->decodeBinary($binary);
            $image->scaleDown(width: $maxWidth, height: $maxHeight);
            $encoded = $image->encode(new WebpEncoder(quality: $quality));

            $storage->makeDirectory($directory);

            if (! $storage->put($path, (string) $encoded)) {
                throw new RuntimeException('ذخیره تصویر انجام نشد.');
            }

            if (! $storage->exists($path)) {
                throw new RuntimeException('فایل تصویر پس از ذخیره پیدا نشد.');
            }

            $finalBinary = $storage->get($path);

            if ($finalBinary === '') {
                throw new RuntimeException('فایل تصویر ذخیره‌شده قابل خواندن نیست.');
            }

            $finalImage = $this->manager->decodeBinary($finalBinary);
            $size = $storage->size($path);

            return [
                'path' => $path,

                'url' => $storage->url($path),

                'filename' => $filename,

                'disk' => $disk,

                'width' => $finalImage->width(),

                'height' => $finalImage->height(),

                'size' => $size,

                'size_kb' => round(
                    $size / 1024,
                    2
                ),

                'mime' => 'image/webp',
            ];
        } catch (Throwable $e) {
            /*
             * Remove partially stored file
             * if something fails.
             */
            try {
                if ($storage->exists($path)) {
                    $storage->delete($path);
                }
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            throw new RuntimeException(
                'پردازش و ذخیره تصویر انجام نشد: '
                .$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Delete an image from storage.
     */
    public function delete(
        ?string $path,
        string $disk = 'public'
    ): bool {
        if (! $path) {
            return false;
        }

        $path = $this->normalizeStoragePath($path);

        if ($path === null) {
            return false;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return false;
        }

        return $storage->delete($path);
    }

    public function deleteAllConverted(): int
    {
        $storage = Storage::disk('public');
        $files = collect($storage->files('converted'))
            ->filter(fn (string $path): bool => Str::lower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp');
        $deleted = 0;

        foreach ($files as $path) {
            if (! $storage->delete($path)) {
                throw new RuntimeException("حذف فایل {$path} انجام نشد.");
            }

            $deleted++;
        }

        return $deleted;
    }

    /**
     * Check whether an image exists.
     */
    public function exists(
        ?string $path,
        string $disk = 'public'
    ): bool {
        if (! $path) {
            return false;
        }

        $path = $this->normalizeStoragePath($path);

        if ($path === null) {
            return false;
        }

        return Storage::disk($disk)->exists($path);
    }

    /**
     * Get public URL of an image.
     */
    public function url(
        ?string $path,
        string $disk = 'public'
    ): ?string {
        if (! $path) {
            return null;
        }

        $path = $this->normalizeStoragePath($path);

        if ($path === null) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * @return array{filename: string, url: string, width: int, height: int, size_bytes: int}
     */
    public function details(string $path, string $disk = 'public'): array
    {
        $path = $this->normalizeStoragePath($path)
            ?? throw new InvalidArgumentException('The image path must be a non-empty relative path.');

        $storage = Storage::disk($disk);
        $image = $this->manager->decodeBinary($storage->get($path));

        return [
            'filename' => basename($path),
            'url' => $storage->url($path),
            'width' => $image->width(),
            'height' => $image->height(),
            'size_bytes' => $storage->size($path),
        ];
    }

    private function uniqueFilename(string $directory, Filesystem $storage): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $filename = Str::uuid().'.webp';

            if (! $storage->exists("{$directory}/{$filename}")) {
                return $filename;
            }
        }

        throw new RuntimeException('نام یکتایی برای تصویر ایجاد نشد؛ دوباره تلاش کنید.');
    }

    private function normalizeStoragePath(string $path): ?string
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path) === 1) {
            return null;
        }

        $segments = explode('/', $path);

        if (in_array('..', $segments, true)) {
            return null;
        }

        $normalizedPath = implode('/', array_filter(
            $segments,
            static fn (string $segment): bool => $segment !== '' && $segment !== '.',
        ));

        return $normalizedPath !== '' ? $normalizedPath : null;
    }
}
