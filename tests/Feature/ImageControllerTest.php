<?php

namespace Tests\Feature;

use App\Services\Shared\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickPixel;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    public function test_home_page_displays_the_upload_form_and_gallery(): void
    {
        config([
            'session.driver' => 'file',
            'cache.default' => 'file',
        ]);

        $response = $this->get(route('images.index'));

        $response->assertOk()
            ->assertViewIs('images.index')
            ->assertSee('تصاویرت را')
            ->assertSee('سبک‌تر کن.')
            ->assertSee('گالری تصاویر');
    }

    public function test_upload_converts_the_image_to_webp_and_saves_it_to_the_gallery_without_database(): void
    {
        Storage::fake('public');

        $response = $this->post(route('images.store'), [
            'image' => $this->validPng(),
            'quality' => 82,
        ]);

        $response->assertRedirectToRoute('images.index')
            ->assertSessionHas('success');

        $files = Storage::disk('public')->allFiles('converted');

        $this->assertCount(1, $files);

        $path = $files[0];
        Storage::disk('public')->assertExists($path);
        $this->assertSame('image/webp', Storage::disk('public')->mimeType($path));
        $this->assertSame(32, app(ImageService::class)->details($path)['width']);

        $this->get(route('images.index'))
            ->assertOk()
            ->assertSee('تصویر با موفقیت به WebP تبدیل و ذخیره شد.');
    }

    public function test_gallery_lists_saved_conversions(): void
    {
        Storage::fake('public');
        $this->createGalleryImage('vacation-photo.webp');

        $this->get(route('images.index'))
            ->assertOk()
            ->assertSee('vacation-photo.webp')
            ->assertSee('WebP')
            ->assertSee('پاک‌سازی همه')
            ->assertSee('https://sajadfarghyari.ir');
    }

    public function test_gallery_paginates_saved_images_without_database(): void
    {
        Storage::fake('public');

        for ($index = 1; $index <= 13; $index++) {
            $this->createGalleryImage("image-{$index}.webp");
        }

        $this->get(route('images.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('صفحه 2 از 2')
            ->assertViewHas('images', fn (LengthAwarePaginator $images): bool => (
                $images->currentPage() === 2 &&
                $images->total() === 13 &&
                $images->count() === 1
            ));
    }

    public function test_upload_rejects_unsupported_files(): void
    {
        Storage::fake('public');

        $response = $this->from(route('images.index'))->post(route('images.store'), [
            'image' => UploadedFile::fake()->createWithContent('notes.txt', 'not an image'),
            'quality' => 85,
        ]);

        $response->assertRedirect(route('images.index'))
            ->assertSessionHasErrors([
                'image' => 'فرمت تصویر پشتیبانی نمی‌شود.',
            ]);

        $this->assertSame([], Storage::disk('public')->allFiles('converted'));
    }

    public function test_upload_rejects_quality_outside_the_supported_range(): void
    {
        Storage::fake('public');

        $response = $this->from(route('images.index'))->post(route('images.store'), [
            'image' => $this->validPng(),
            'quality' => 101,
        ]);

        $response->assertRedirect(route('images.index'))
            ->assertSessionHasErrors([
                'quality' => 'کیفیت خروجی باید بین ۶۰ تا ۱۰۰ باشد.',
            ]);

        $this->assertSame([], Storage::disk('public')->allFiles('converted'));
    }

    public function test_delete_removes_the_image_from_storage_and_the_gallery(): void
    {
        Storage::fake('public');
        $this->createGalleryImage('to-delete.webp');

        $response = $this->delete(route('images.destroy', 'to-delete.webp'));

        $response->assertRedirectToRoute('images.index')
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing('converted/to-delete.webp');
    }

    public function test_delete_accepts_uppercase_webp_extensions(): void
    {
        Storage::fake('public');
        $this->createGalleryImage('uppercase.WEBP');

        $response = $this->delete(route('images.destroy', 'uppercase.WEBP'));

        $response->assertRedirectToRoute('images.index')
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing('converted/uppercase.WEBP');
    }

    public function test_upload_rejects_images_that_exceed_the_pixel_limit(): void
    {
        Storage::fake('public');

        $response = $this->from(route('images.index'))->post(route('images.store'), [
            'image' => $this->pngWithDimensions(8000, 6000),
            'quality' => 85,
        ]);

        $response->assertRedirect(route('images.index'))
            ->assertSessionHasErrors('image');

        $this->assertSame([], Storage::disk('public')->allFiles('converted'));
    }

    public function test_image_service_refuses_paths_that_traverse_the_storage_root(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('outside.webp', 'keep this file');

        $deleted = app(ImageService::class)->delete('converted/../outside.webp');

        $this->assertFalse($deleted);
        Storage::disk('public')->assertExists('outside.webp');
    }

    public function test_bulk_delete_removes_all_converted_images_without_removing_other_files(): void
    {
        Storage::fake('public');
        $this->createGalleryImage('first.webp');
        $this->createGalleryImage('second.webp');
        Storage::disk('public')->put('converted/keep.txt', 'keep this file');

        $response = $this->delete(route('images.destroy-all'));

        $response->assertRedirectToRoute('images.index')
            ->assertSessionHas('success', 'همهٔ 2 تصویر ذخیره‌شده پاک شدند.');

        Storage::disk('public')->assertMissing('converted/first.webp');
        Storage::disk('public')->assertMissing('converted/second.webp');
        Storage::disk('public')->assertExists('converted/keep.txt');
    }

    private function validPng(): UploadedFile
    {
        $image = new Imagick;
        $image->newImage(32, 32, new ImagickPixel('#4389f5'));
        $image->setImageFormat('png');

        $contents = $image->getImagesBlob();
        $image->clear();

        return UploadedFile::fake()->createWithContent('sample.png', $contents);
    }

    private function pngWithDimensions(int $width, int $height): UploadedFile
    {
        $image = $this->validPng();
        $contents = file_get_contents($image->getRealPath());

        $this->assertNotFalse($contents);

        $contents = substr_replace($contents, pack('N2', $width, $height), 16, 8);

        return UploadedFile::fake()->createWithContent('large.png', $contents);
    }

    private function createGalleryImage(string $filename): void
    {
        $image = new Imagick;
        $image->newImage(32, 32, new ImagickPixel('#4389f5'));
        $image->setImageFormat('webp');

        Storage::disk('public')->put("converted/{$filename}", $image->getImagesBlob());
        $image->clear();
    }
}
