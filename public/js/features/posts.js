(function () {
    const input = document.querySelector('#select_post_img');
    const image = document.querySelector('#post_img');
    const uploadZone = document.querySelector('.hb-upload-zone');
    const removeBtn = document.querySelector('#remove_post_img');
    const form = document.querySelector('#form_add_post');
    const textarea = document.querySelector('#post_text_input');

    if (input && image) {
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function () {
                image.setAttribute('src', reader.result);
                image.style.display = 'block';
                if (uploadZone) uploadZone.classList.add('has-image');
                if (removeBtn) removeBtn.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeBtn && input && image) {
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            input.value = '';
            image.setAttribute('src', '');
            image.style.display = 'none';
            if (uploadZone) uploadZone.classList.remove('has-image');
            removeBtn.style.display = 'none';
        });
    }

    // When clicking "Ảnh / Video" in composer, open modal and trigger file selector
    document.querySelectorAll('[data-action="open-post-photo"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const modalEl = document.querySelector('#addpost');
            if (!modalEl) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            setTimeout(function () {
                if (input) input.click();
            }, 350);
        });
    });

    // Form submit validation: require at least text or image
    if (form) {
        form.addEventListener('submit', function (e) {
            const hasText = textarea && textarea.value.trim().length > 0;
            const hasFile = input && input.files && input.files.length > 0;
            if (!hasText && !hasFile) {
                e.preventDefault();
                alert('Vui lòng nhập nội dung bài viết hoặc chọn ảnh để đăng.');
                if (textarea) textarea.focus();
            }
        });
    }
})();
