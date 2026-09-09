(function ($) {
    if (!$) return;
    $('#search').focus(function(){ $('#search_result').show(); });
    $('#close_search').click(function(){ $('#search_result').hide(); });
    $('#search').keyup(function(){ const keyword=$(this).val(); if(!keyword){$('#sra').html('');return;} $.ajax({url:'?api=search',method:'post',dataType:'json',data:{keyword:keyword},success:function(r){$('#sra').html(r.status?r.users:'<p class="text-center text-muted">không tìm thấy người dùng nào!</p>');}}); });
    $(document).click(function(e){ if(!$(e.target).closest('#searchform').length) $('#search_result').hide(); });
})(window.jQuery);
