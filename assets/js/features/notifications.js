(function ($) {
    if (!$) return;

    $('#show_not').click(function () {
        $.ajax({
            url: 'assets/php/ajax.php?notread',
            method: 'post',
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    $('.un-count').hide();
                }
            }
        });
    });
})(window.jQuery);
