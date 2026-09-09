(function ($) {
    if (!$) return;

    $('#search').focus(function () {
        $('#search_result').show();
    });

    $('#close_search').click(function () {
        $('#search_result').hide();
    });

    $('#search').keyup(function () {
        const keyword = $(this).val();
        if (!keyword) {
            $('#sra').html('');
            return;
        }

        $.ajax({
            url: 'assets/php/ajax.php?search',
            method: 'post',
            dataType: 'json',
            data: { keyword: keyword },
            success: function (response) {
                if (response.status) {
                    $('#sra').html(response.users);
                } else {
                    $('#sra').html('<p class="text-center text-muted">không tìm thấy người dùng nào!</p>');
                }
            }
        });
    });
})(window.jQuery);
