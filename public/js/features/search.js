(function ($) {
    if (!$) return;
    let timer = null;
    $('#search').focus(function(){ $('#search_result').show(); });
    $('#close_search').click(function(){ $('#search_result').hide(); });

    function renderUsers(users) {
        const root = $('#sra').empty();
        if (!users || !users.length) {
            root.append($('<p>').addClass('text-center text-muted').text('Không tìm thấy người dùng nào!'));
            return;
        }
        users.forEach(function(user){
            const row = $('<div>').addClass('d-flex align-items-center p-2');
            $('<img>').attr({src:'public/images/profile/' + (user.profile_pic || 'default_profile.jpg'),height:40,width:40,alt:''}).addClass('rounded-circle border').appendTo(row);
            const box = $('<div>').addClass('ms-2').appendTo(row);
            $('<a>').attr('href','?u='+encodeURIComponent(user.username || '')).addClass('text-decoration-none text-dark fw-bold').text((user.first_name || '')+' '+(user.last_name || '')).appendTo(box);
            $('<p>').addClass('mb-0 small text-muted').text('@'+(user.username || '')).appendTo(box);
            root.append(row);
        });
    }

    $('#search').on('input', function(){
        const keyword=String($(this).val()||'').trim();
        clearTimeout(timer);
        if(!keyword){ $('#sra').empty().append($('<p>').addClass('text-center text-muted').text('Nhập tên hoặc tên người dùng')); return; }
        timer=setTimeout(function(){
            $.ajax({url:'?api=search',method:'post',dataType:'json',data:{keyword:keyword}})
                .done(function(r){ renderUsers(r.users || []); })
                .fail(function(){ renderUsers([]); });
        },250);
    });
    $(document).click(function(e){ if(!$(e.target).closest('#searchform').length) $('#search_result').hide(); });
})(window.jQuery);
