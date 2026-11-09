<?php

namespace App\Http\Controllers;

use App\Services\Shared\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class ImageController extends Controller
{
    public function index(Request $request, ImageService $imageService): View
    {
        $storage = Storage::disk('public');
        $files = collect($storage->files('converted'))
            ->filter(fn (string $path): bool => Str::lower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp')
            ->map(fn (string $path): array => [
                'path' => $path,
                'modified' => $storage->lastModified($path),
            ])
            ->sort(fn (array $first, array $second): int => (
                $second['modified'] <=> $first['modified']
            ) ?: strcmp($second['path'], $first['path']))
            ->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $images = $files->forPage($page, 12)
            ->map(fn (array $file): array => $imageService->details($file['path'], 'public'));

        $images = new LengthAwarePaginator(
            $images,
            $files->count(),
            12,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return view('images.index', [
            'images' => $images,
        ]);
    }

    public function store(Request $request, ImageService $imageService): RedirectResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff,avif',
                'extensions:jpg,jpeg,png,webp,gif,bmp,tif,tiff,avif',
                'max:10240',
            ],
            'quality' => ['required', 'integer', 'between:60,100'],
        ], [
            'image.required' => 'لطفاً یک تصویر انتخاب کنید.',
            'image.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'image.mimes' => 'فرمت تصویر پشتیبانی نمی‌شود.',
            'image.extensions' => 'پسوند فایل انتخاب‌شده معتبر نیست.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۱۰ مگابایت باشد.',
            'quality.required' => 'کیفیت خروجی را مشخص کنید.',
            'quality.integer' => 'کیفیت خروجی باید یک عدد باشد.',
            'quality.between' => 'کیفیت خروجی باید بین ۶۰ تا ۱۰۰ باشد.',
        ]);

        $uploadedFile = $validated['image'];

        try {
            $imageService->store(
                $uploadedFile,
                'converted',
                1920,
                1920,
                (int) $validated['quality'],
                'public',
            );
        } catch (RuntimeException $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'image' => 'تبدیل تصویر انجام نشد. لطفاً یک تصویر معتبر انتخاب کنید.',
                ]);
        }

        return redirect()
            ->route('images.index')
            ->with('success', 'تصویر با موفقیت به WebP تبدیل و ذخیره شد.');
    }

    public function destroy(
        string $filename,
        ImageService $imageService,
    ): RedirectResponse {
        abort_unless(
            $filename === basename($filename)
                && Str::lower(pathinfo($filename, PATHINFO_EXTENSION)) === 'webp',
            404,
        );

        $path = "converted/{$filename}";

        abort_unless($imageService->exists($path, 'public'), 404);

        if (! $imageService->delete($path, 'public')) {
            return back()->withErrors([
                'gallery' => 'حذف فایل انجام نشد. دوباره تلاش کنید.',
            ]);
        }

        return redirect()
            ->route('images.index')
            ->with('success', 'تصویر از گالری حذف شد.');
    }

    public function destroyAll(ImageService $imageService): RedirectResponse
    {
        try {
            $deleted = $imageService->deleteAllConverted();
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->withErrors([
                'gallery' => 'پاک‌سازی کامل نشد. لطفاً دوباره تلاش کنید.',
            ]);
        }

        $message = $deleted > 0
            ? "همهٔ {$deleted} تصویر ذخیره‌شده پاک شدند."
            : 'تصویری برای پاک‌کردن وجود نداشت.';

        return redirect()
            ->route('images.index')
            ->with('success', $message);
    }
}
