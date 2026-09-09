(function ($) {
    if (!$ || !document.querySelector('#chatlist')) return;

    let chattingUserId = 0;

    window.popchat = function (userId) {
        $('#user_chat').html('<div class="spinner-border text-center" role="status"></div>');
        $('#chatter_username').text('loading..');
        $('#chatter_name').text('');
        $('#chatter_pic').attr('src', 'assets/images/profile/default_profile.jpg');
        chattingUserId = Number(userId) || 0;
        $('#sendmsg').attr('data-user-id', chattingUserId);
    };

    $('#sendmsg').click(function () {
        const message = $('#msginput').val();
        if (!message || chattingUserId <= 0) return;

        $('#sendmsg').attr('disabled', true);
        $('#msginput').attr('disabled', true);

        $.ajax({
            url: 'assets/php/ajax.php?sendmessage',
            method: 'post',
            dataType: 'json',
            data: { user_id: chattingUserId, msg: message },
            success: function (response) {
                $('#sendmsg').attr('disabled', false);
                $('#msginput').attr('disabled', false);

                if (response.status) {
                    $('#msginput').val('');
                    syncMessages();
                } else {
                    alert('Không thể gửi tin nhắn. Vui lòng thử lại.');
                }
            }
        });
    });

    function syncMessages() {
        $.ajax({
            url: 'assets/php/ajax.php?getmessages',
            method: 'post',
            dataType: 'json',
            data: { chatter_id: chattingUserId },
            success: function (response) {
                $('#chatlist').html(response.chatlist || '');

                if (Number(response.newmsgcount) === 0) {
                    $('#msgcounter').hide();
                } else {
                    $('#msgcounter').show().html('<small>' + response.newmsgcount + '</small>');
                }

                if (response.blocked) {
                    $('#msgsender').hide();
                    $('#blerror').show();
                } else {
                    $('#msgsender').show();
                    $('#blerror').hide();
                }

                if (chattingUserId > 0 && response.chat && response.chat.userdata) {
                    $('#user_chat').html(response.chat.msgs || '');
                    $('#chatter_username').text(response.chat.userdata.username);
                    $('#cplink').attr('href', '?u=' + encodeURIComponent(response.chat.userdata.username));
                    $('#chatter_name').text(response.chat.userdata.first_name + ' ' + response.chat.userdata.last_name);
                    $('#chatter_pic').attr('src', 'assets/images/profile/' + response.chat.userdata.profile_pic);
                }
            }
        });
    }

    syncMessages();
    window.setInterval(syncMessages, 1000);
})(window.jQuery);
