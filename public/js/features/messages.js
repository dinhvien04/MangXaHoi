(function ($) {
    if (!$ || !document.querySelector('#chatlist')) return;
    let chattingUserId = 0;
    let syncing = false;

    function messageNode(message, currentUserId) {
        const mine = Number(message.from_user_id) === Number(currentUserId);
        const node = $('<div>').addClass('py-2 px-3 border rounded shadow-sm col-8 d-inline-block').toggleClass('align-self-end bg-primary text-light', mine);
        node.append(document.createTextNode(message.msg || ''), $('<br>'), $('<span>').addClass('small').toggleClass('text-light', mine).toggleClass('text-muted', !mine).text(message.created_at || ''));
        return node;
    }

    function renderConversations(items) {
        const root = $('#chatlist').empty();
        if (!items || !items.length) {
            root.append($('<p>').addClass('text-center text-muted').text('Chưa có cuộc trò chuyện nào.'));
            return;
        }
        items.forEach(function(item){
            const user=item.user||{}, latest=item.latest_message||{};
            const row=$('<div>').addClass('d-flex justify-content-between border-bottom chatlist_item').css('cursor','pointer').on('click',function(){ window.popchat(user.id); const modal=bootstrap.Modal.getOrCreateInstance(document.getElementById('chatbox')); modal.show(); });
            const left=$('<div>').addClass('d-flex align-items-center p-2').appendTo(row);
            $('<img>').attr({src:'public/images/profile/'+(user.profile_pic||'default_profile.jpg'),height:40,width:40,alt:''}).addClass('rounded-circle border').appendTo(left);
            const text=$('<div>').addClass('ms-2').appendTo(left);
            $('<strong>').text((user.first_name||'')+' '+(user.last_name||'')).appendTo(text);
            $('<p>').addClass('mb-0 small').text(latest.msg||'').appendTo(text);
            $('<small>').addClass('text-muted').text(latest.created_at||'').appendTo(text);
            if (Number(latest.read_status) === 0 && Number(latest.to_user_id) !== Number(user.id)) $('<span>').addClass('p-1 bg-primary rounded-circle me-2').appendTo(row);
            root.append(row);
        });
    }

    window.popchat = function(userId){
        chattingUserId=Number(userId)||0;
        $('#user_chat').empty().append($('<div>').addClass('spinner-border text-center').attr('role','status'));
        syncMessages();
    };

    $('#sendmsg').click(function(){
        const message=String($('#msginput').val()||'').trim();
        if(!message||chattingUserId<=0)return;
        $('#sendmsg,#msginput').prop('disabled',true);
        $.ajax({url:'?api=send_message',method:'post',dataType:'json',data:{user_id:chattingUserId,msg:message}})
            .done(function(r){if(r.status){$('#msginput').val('');syncMessages();}})
            .fail(function(){alert('Không thể gửi tin nhắn.');})
            .always(function(){$('#sendmsg,#msginput').prop('disabled',false);});
    });

    function syncMessages(){
        if(syncing || document.hidden) return;
        syncing=true;
        $.ajax({url:'?api=get_messages',method:'post',dataType:'json',data:{chatter_id:chattingUserId}})
            .done(function(r){
                if(!r.status)return;
                renderConversations(r.conversations||[]);
                const count=Number(r.new_message_count)||0;
                if(count===0)$('#msgcounter').hide();else $('#msgcounter').show().text(count);
                if(r.blocked){$('#msgsender').hide();$('#blerror').show();}else{$('#msgsender').show();$('#blerror').hide();}
                if(chattingUserId>0&&r.chat&&r.chat.user){
                    const user=r.chat.user;
                    $('#chatter_username').text(user.username||'');
                    $('#cplink').attr('href','?u='+encodeURIComponent(user.username||''));
                    $('#chatter_name').text((user.first_name||'')+' '+(user.last_name||''));
                    $('#chatter_pic').attr('src','public/images/profile/'+(user.profile_pic||'default_profile.jpg'));
                    const chat=$('#user_chat').empty();
                    (r.chat.messages||[]).forEach(function(message){chat.append(messageNode(message, window.currentUserId||0));});
                }
            })
            .always(function(){syncing=false;});
    }

    syncMessages();
    window.setInterval(syncMessages,5000);
})(window.jQuery);
