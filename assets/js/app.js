(function ($) {
    if (!$) return;

    $(function () {
        if ($.fn.timeago) {
            $('time.timeago').timeago();
        }
    });
})(window.jQuery);
