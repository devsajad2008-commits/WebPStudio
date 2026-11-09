<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>وب‌پی استودیو | تبدیل تصویر به WebP</title>
    <meta name="description" content="تصاویرت را با چند کلیک به WebP تبدیل کن و در گالری نگه دار.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="{{ route('images.index') }}" aria-label="وب‌پی استودیو، صفحه اصلی">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none">
                        <path d="M7 8.5A3.5 3.5 0 0 1 10.5 5h11A3.5 3.5 0 0 1 25 8.5v15a3.5 3.5 0 0 1-3.5 3.5h-11A3.5 3.5 0 0 1 7 23.5v-15Z" stroke="currentColor" stroke-width="2"/>
                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                        <path d="m8 22 5.2-5.3a1.2 1.2 0 0 1 1.7 0l2 2 2.2-2.2a1.2 1.2 0 0 1 1.7 0L25 20.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="brand-name">وب‌پی<span>استودیو</span></span>
            </a>
            <div class="topbar-note">
                <span class="status-dot"></span>
                ساده، سریع و همیشه در دسترس
            </div>
        </header>

        <main>
            @if (session('success') || $errors->has('gallery'))
                <div class="page-notices">
                    @if (session('success'))
                        <div class="notice notice-success" role="status">
                            <span class="notice-check">✓</span>
                            {{ session('success') }}
                        </div>
                    @endif
                    @error('gallery')
                        <div class="notice notice-error" role="alert">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <section class="hero">
                <div class="hero-copy">
                    <p class="eyebrow"><span class="eyebrow-line"></span> ابزار هوشمند تصویر</p>
                    <h1>تصاویرت را<br><span>سبک‌تر کن.</span></h1>
                    <p class="hero-description">
                        کیفیتی که می‌بینی، حجمی که دوست داری. عکس‌هایت را به WebP تبدیل کن و همه را یک‌جا نگه دار.
                    </p>
                    <div class="hero-points">
                        <span>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 10 3.2 3.2L15.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            کیفیت قابل تنظیم
                        </span>
                        <span>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 10 3.2 3.2L15.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            خروجی کم‌حجم WebP
                        </span>
                    </div>
                    <a class="text-link" href="#gallery">
                        دیدن گالری
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4v12m0 0 4-4m-4 4-4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <div class="hero-orbit orbit-one"></div>
                    <div class="hero-orbit orbit-two"></div>
                </div>

                <section class="converter-card" aria-labelledby="converter-title">
                    <div class="card-heading">
                        <span class="heading-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M13 3 4 14h7l-1 7 10-12h-7l1-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <h2 id="converter-title">شروع تبدیل</h2>
                            <p>تصویرت را انتخاب کن؛ بقیه‌اش با ما</p>
                        </div>
                        <span class="card-step">۰۱ <i>/ ۰۱</i></span>
                    </div>

                    <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data" data-upload-form>
                        @csrf
                        <input
                            class="visually-hidden"
                            id="image"
                            name="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,image/tiff,image/avif"
                            required
                        >
                        <label class="upload-zone" for="image" data-dropzone tabindex="0">
                            <span class="upload-icon" aria-hidden="true">
                                <svg viewBox="0 0 28 28" fill="none"><path d="M14 18V5m0 0L9.5 9.5M14 5l4.5 4.5M6 17v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="upload-title">تصویرت را اینجا رها کن</span>
                            <span class="upload-subtitle">یا برای انتخاب فایل کلیک کن</span>
                            <span class="file-types">JPG&nbsp; · &nbsp;PNG&nbsp; · &nbsp;WEBP&nbsp; · &nbsp;GIF&nbsp; · &nbsp;BMP&nbsp; · &nbsp;TIFF&nbsp; · &nbsp;AVIF</span>
                            <span class="file-limit">حداکثر حجم فایل: ۱۰ مگابایت</span>
                        </label>
                        <span class="field-error" data-upload-error role="alert" hidden></span>
                        @error('image')
                            <span class="field-error" role="alert">{{ $message }}</span>
                        @enderror

                        <div class="selected-file" data-selected-file role="status" aria-live="polite" hidden>
                            <span class="selected-thumb">
                                <img src="" alt="" data-preview-image>
                            </span>
                            <span class="selected-details">
                                <strong data-file-name></strong>
                                <small data-file-size></small>
                            </span>
                            <span class="selected-check" aria-label="فایل انتخاب شد">✓</span>
                        </div>

                        <div class="quality-control">
                            <div class="quality-label">
                                <label for="quality">کیفیت خروجی</label>
                                <span><b data-quality-value>۸۵</b><small>٪</small></span>
                            </div>
                            <input id="quality" name="quality" type="range" min="60" max="100" value="{{ old('quality', 85) }}" step="1" data-quality>
                            <div class="range-labels"><span>حجم کمتر</span><span>کیفیت بالاتر</span></div>
                            @error('quality')
                                <span class="field-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <button class="convert-button" type="submit" data-submit-button>
                            <span data-submit-label>تبدیل تصویر به WebP</span>
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h12m0 0-4-4m4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>

                    <p class="privacy-note">
                        <svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M9 2.5 3.5 5v3.7c0 3.2 2.2 5.8 5.5 6.8 3.3-1 5.5-3.6 5.5-6.8V5L9 2.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="m6.8 8.9 1.5 1.5 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        فایل اصلی نگه‌داری نمی‌شود؛ فقط نسخه WebP در گالری ذخیره می‌شود.
                    </p>
                </section>
            </section>

            <section class="gallery-section" id="gallery" aria-labelledby="gallery-title">
                <div class="gallery-heading">
                    <div>
                        <p class="eyebrow"><span class="eyebrow-line"></span> آرشیو شما</p>
                        <h2 id="gallery-title">گالری تصاویر</h2>
                        <p class="gallery-description">همه‌ی تبدیل‌ها، مرتب و آماده‌ی استفاده</p>
                    </div>
                    <div class="gallery-tools">
                        @if ($images->total() > 0)
                            <form action="{{ route('images.destroy-all') }}" method="POST" class="gallery-toolbar-form" data-clear-form data-image-count="{{ $images->total() }}">
                                @csrf
                                @method('DELETE')
                                <button class="clear-gallery-button" type="submit">
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.5 6h11m-9.5 0 .6 10h6.8l.6-10M8 6V4h4v2m-3 3v4m2-4v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    پاک‌سازی همه
                                </button>
                            </form>
                        @endif
                        <div class="gallery-count">
                            <span class="count-number">{{ number_format($images->total()) }}</span>
                            <span>تصویر تبدیل‌شده</span>
                        </div>
                    </div>
                </div>

                @if ($images->isNotEmpty())
                    <div class="gallery-grid">
                        @foreach ($images as $image)
                            <article class="image-card">
                                <div class="image-preview">
                                    <img src="{{ $image['url'] }}" alt="{{ $image['filename'] }}" loading="lazy">
                                    <span class="format-badge"><span></span> WEBP</span>
                                    <a class="image-open" href="{{ $image['url'] }}" target="_blank" rel="noopener" aria-label="نمایش {{ $image['filename'] }}">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M11 4h5v5m-1-4-7 7M14 11v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                </div>
                                <div class="image-card-body">
                                    <div class="image-meta">
                                        <strong title="{{ $image['filename'] }}">{{ $image['filename'] }}</strong>
                                        <span>{{ number_format($image['width']) }} × {{ number_format($image['height']) }} پیکسل</span>
                                    </div>
                                    <div class="image-card-footer">
                                        <span class="file-size">{{ number_format($image['size_bytes'] / 1024, 1) }} KB</span>
                                        <div class="image-actions">
                                            <a class="download-button" href="{{ $image['url'] }}" download="{{ $image['filename'] }}" aria-label="دانلود {{ $image['filename'] }}">
                                                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 3.5v9m0 0 3.2-3.2M10 12.5 6.8 9.3M4 13v2.5a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                            <form action="{{ route('images.destroy', $image['filename']) }}" method="POST" data-delete-form>
                                                @csrf
                                                @method('DELETE')
                                                <button class="delete-button" type="submit" aria-label="حذف {{ $image['filename'] }}">
                                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.5 6h11m-9.5 0 .6 10h6.8l.6-10M8 6V4h4v2m-3 3v4m2-4v4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($images->hasPages())
                        <nav class="pagination" aria-label="صفحه‌های گالری">
                            @if ($images->previousPageUrl())
                                <a href="{{ $images->previousPageUrl() }}" class="page-link">
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m12 4-6 6 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    قبلی
                                </a>
                            @else
                                <span class="page-link is-disabled">
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m12 4-6 6 6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    قبلی
                                </span>
                            @endif
                            <span class="page-status">صفحه {{ $images->currentPage() }} از {{ $images->lastPage() }}</span>
                            @if ($images->nextPageUrl())
                                <a href="{{ $images->nextPageUrl() }}" class="page-link">
                                    بعدی
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m8 4 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            @else
                                <span class="page-link is-disabled">
                                    بعدی
                                    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m8 4 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            @endif
                        </nav>
                    @endif
                @else
                    <div class="empty-gallery">
                        <span class="empty-icon" aria-hidden="true">
                            <svg viewBox="0 0 48 48" fill="none"><rect x="8" y="8" width="32" height="32" rx="8" stroke="currentColor" stroke-width="1.8"/><circle cx="19" cy="19" r="3" stroke="currentColor" stroke-width="1.8"/><path d="m10 33 10-9 6 5 5-4 7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <h3>هنوز تصویری اینجا نیست</h3>
                        <p>اولین تصویرت را تبدیل کن تا اینجا ببینیش.</p>
                        <button class="empty-link" type="button" data-browse-images>انتخاب تصویر</button>
                    </div>
                @endif
            </section>
        </main>

        <footer class="footer">
            <a class="brand footer-brand" href="{{ route('images.index') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M7 8.5A3.5 3.5 0 0 1 10.5 5h11A3.5 3.5 0 0 1 25 8.5v15a3.5 3.5 0 0 1-3.5 3.5h-11A3.5 3.5 0 0 1 7 23.5v-15Z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2" fill="currentColor"/><path d="m8 22 5.2-5.3a1.2 1.2 0 0 1 1.7 0l2 2 2.2-2.2a1.2 1.2 0 0 1 1.7 0L25 20.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
                <span class="brand-name">وب‌پی<span>استودیو</span></span>
            </a>
            <span class="footer-credit">طراحی‌شده توسط <a href="https://sajadfarghyari.ir" target="_blank" rel="noopener noreferrer">sajadfarghyari.ir</a></span>
        </footer>
    </div>
</body>
</html>
