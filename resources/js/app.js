import '@fontsource/vazirmatn/400.css';
import '@fontsource/vazirmatn/500.css';
import '@fontsource/vazirmatn/600.css';
import '@fontsource/vazirmatn/700.css';
import '@fontsource/vazirmatn/800.css';
import '@fontsource/vazirmatn/900.css';

const fileInput = document.querySelector('#image');
const dropzone = document.querySelector('[data-dropzone]');
const selectedFile = document.querySelector('[data-selected-file]');
const previewImage = document.querySelector('[data-preview-image]');
const qualityInput = document.querySelector('[data-quality]');
const qualityValue = document.querySelector('[data-quality-value]');
const uploadForm = document.querySelector('[data-upload-form]');
const uploadError = document.querySelector('[data-upload-error]');
const browseImagesButton = document.querySelector('[data-browse-images]');
const maxUploadBytes = 10 * 1024 * 1024;
const allowedExtensions = new Set(['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tif', 'tiff', 'avif']);
const fileSizeFormatter = new Intl.NumberFormat('fa-IR', {
    maximumFractionDigits: 1,
    minimumFractionDigits: 1,
});
let previewUrl = null;

const setUploadError = (message) => {
    if (!uploadError) {
        return;
    }

    uploadError.textContent = message;
    uploadError.hidden = message === '';
};

if (fileInput && dropzone && selectedFile && previewImage) {
    const showSelectedFile = (file) => {
        if (!file) {
            selectedFile.hidden = true;
            previewImage.removeAttribute('src');
            previewImage.hidden = true;

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            return;
        }

        selectedFile.hidden = false;
        selectedFile.querySelector('[data-file-name]').textContent = file.name;
        selectedFile.querySelector('[data-file-size]').textContent =
            `${fileSizeFormatter.format(file.size / 1024)} کیلوبایت`;

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (file.type.startsWith('image/')) {
            previewUrl = URL.createObjectURL(file);
            previewImage.src = previewUrl;
            previewImage.hidden = false;
        } else {
            previewImage.removeAttribute('src');
            previewImage.hidden = true;
        }
    };

    const selectFile = (file) => {
        const extension = file.name.split('.').pop()?.toLowerCase();

        if (!extension || !allowedExtensions.has(extension)) {
            fileInput.value = '';
            showSelectedFile(null);
            setUploadError('فرمت تصویر پشتیبانی نمی‌شود.');
            return false;
        }

        if (file.size > maxUploadBytes) {
            fileInput.value = '';
            showSelectedFile(null);
            setUploadError('حجم تصویر نباید بیشتر از ۱۰ مگابایت باشد.');
            return false;
        }

        setUploadError('');
        showSelectedFile(file);

        return true;
    };

    fileInput.addEventListener('change', () => {
        const [file] = fileInput.files;

        if (file) {
            selectFile(file);
        }
    });

    dropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
        }
    });

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragging');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('is-dragging');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');

        const [file] = event.dataTransfer.files;

        if (!file) {
            return;
        }

        if (!selectFile(file)) {
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        fileInput.files = transfer.files;
    });
}

if (browseImagesButton && fileInput) {
    browseImagesButton.addEventListener('click', () => fileInput.click());
}

if (qualityInput && qualityValue) {
    const persianNumber = new Intl.NumberFormat('fa-IR');
    const updateQuality = () => {
        qualityValue.textContent = persianNumber.format(qualityInput.value);
    };

    qualityInput.addEventListener('input', updateQuality);
    updateQuality();
}

if (uploadForm) {
    uploadForm.addEventListener('submit', () => {
        const submitButton = uploadForm.querySelector('[data-submit-button]');
        const submitLabel = uploadForm.querySelector('[data-submit-label]');

        if (submitButton && submitLabel) {
            submitButton.disabled = true;
            submitLabel.textContent = 'در حال تبدیل تصویر...';
        }
    });
}

document.querySelectorAll('[data-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('این تصویر از گالری حذف شود؟')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-clear-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const imageCount = new Intl.NumberFormat('fa-IR').format(form.dataset.imageCount);
        const confirmed = window.confirm(
            `تمام ${imageCount} تصویر ذخیره‌شده برای همیشه پاک شوند؟`,
        );

        if (!confirmed) {
            event.preventDefault();
        }
    });
});
