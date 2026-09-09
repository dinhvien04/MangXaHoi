(function () {
    const input = document.querySelector('#select_post_img');
    if (!input) return;

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        const image = document.querySelector('#post_img');
        if (!file || !image) return;

        const reader = new FileReader();
        reader.onload = function () {
            image.setAttribute('src', reader.result);
            image.style.display = '';
        };
        reader.readAsDataURL(file);
    });
})();
