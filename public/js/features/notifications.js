(function ($) {
    if (!$) return;
    $('#show_not').click(function(){
        $.ajax({url:'?api=notifications_read',method:'post',dataType:'json'})
            .done(function(r){ if(r.status) $('#show_not .un-count').hide(); });
    });
})(window.jQuery);
