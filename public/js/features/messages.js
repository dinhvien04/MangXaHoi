(function ($) {
    'use strict';
    if (!$ || !document.querySelector('#chatlist')) return;
    let chattingUserId = 0;
    let syncing = false;

    function messageNode(message, currentUserId) {
        const mine = Number(message.from_user_id) === Number(currentUserId);
        const wrap = $('<div>').addClass('hb-chat-message').toggleClass('is-mine', mine);
        $('<div>').addClass('hb-chat-bubble').text(message.msg || '').appendTo(wrap);
        $('<time>').addClass('hb-chat-time').text(message.created_at || '').appendTo(wrap);
        return wrap;
    }

    function applyConversationFilter() {
        const input = document.getElementById('conversation_search');
        if (!input) return;
        const needle = String(input.value || '').trim().toLocaleLowerCase('vi');
        document.querySelectorAll('#chatlist .chatlist_item').forEach(function (row) {
            row.hidden = !!needle && !row.textContent.toLocaleLowerCase('vi').includes(needle);
        });
    }

    function renderConversations(items) {
        const root = $('#chatlist').empty();
        if (!items || !items.length) {
            root.append($('<div>').addClass('hb-panel-empty').append($('<i>').addClass('bi bi-chat-square-text'), $('<strong>').text('Chưa có cuộc trò chuyện'), $('<span>').text('Tin nhắn mới sẽ xuất hiện ở đây.')));
            return;
        }
        items.forEach(function (item) {
            const user = item.user || {};
            const latest = item.latest_message || {};
            const row = $('<button>').attr('type', 'button').addClass('hb-conversation-row chatlist_item');
            $('<img>').attr({src: 'public/images/profile/' + (user.profile_pic || 'default_profile.jpg'), alt: 'Ảnh đại diện của ' + ((user.first_name || '') + ' ' + (user.last_name || '')).trim()}).appendTo(row);
            const copy = $('<span>').addClass('hb-conversation-copy').appendTo(row);
            $('<strong>').text(((user.first_name || '') + ' ' + (user.last_name || '')).trim()).appendTo(copy);
            $('<span>').text(latest.msg || 'Bắt đầu cuộc trò chuyện').appendTo(copy);
            const meta = $('<span>').addClass('hb-conversation-meta').appendTo(row);
            $('<time>').text(latest.created_at || '').appendTo(meta);
            if (Number(latest.read_status) === 0 && Number(latest.to_user_id) !== Number(user.id)) $('<span>').addClass('hb-conversation-unread').attr('aria-label', 'Tin nhắn chưa đọc').appendTo(meta);
            row.on('click', function () { window.popchat(user.id); bootstrap.Modal.getOrCreateInstance(document.getElementById('chatbox')).show(); });
            root.append(row);
        });
        applyConversationFilter();
    }

    window.popchat = function (userId) {
        chattingUserId = Number(userId) || 0;
        $('#user_chat').empty().append($('<div>').addClass('hb-panel-empty').append($('<span>').addClass('spinner-border spinner-border-sm').attr('role', 'status')));
        syncMessages();
    };

    function sendMessage() {
        const message = String($('#msginput').val() || '').trim();
        if (!message || chattingUserId <= 0) return;
        $('#sendmsg,#msginput').prop('disabled', true);
        $.ajax({url: '?api=send_message', method: 'post', dataType: 'json', data: {user_id: chattingUserId, msg: message}})
            .done(function (response) { if (response.status) { $('#msginput').val(''); syncMessages(); } })
            .fail(function (xhr) {
                const text = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Không thể gửi tin nhắn.';
                if (window.showHandbookToast) window.showHandbookToast(text, 'danger'); else alert(text);
            })
            .always(function () { $('#sendmsg,#msginput').prop('disabled', false); $('#msginput').trigger('focus'); });
    }

    $('#sendmsg').on('click', sendMessage);
    $('#msginput').on('keydown', function (event) { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendMessage(); } });
    $('#conversation_search').on('input', applyConversationFilter);

    function syncMessages() {
        if (syncing || document.hidden) return;
        syncing = true;
        $.ajax({url: '?api=get_messages', method: 'post', dataType: 'json', data: {chatter_id: chattingUserId}})
            .done(function (response) {
                if (!response.status) return;
                renderConversations(response.conversations || []);
                const count = Number(response.new_message_count) || 0;
                if (count === 0) $('#msgcounter').hide(); else $('#msgcounter').show().text(count);
                if (response.blocked) { $('#msgsender').hide(); $('#blerror').show(); } else { $('#msgsender').show(); $('#blerror').hide(); }
                if (chattingUserId > 0 && response.chat && response.chat.user) {
                    const user = response.chat.user;
                    $('#chatter_username').text(user.username || '');
                    $('#cplink').attr('href', '?u=' + encodeURIComponent(user.username || ''));
                    $('#chatter_name').text(((user.first_name || '') + ' ' + (user.last_name || '')).trim());
                    $('#chatter_pic').attr('src', 'public/images/profile/' + (user.profile_pic || 'default_profile.jpg'));
                    const chat = $('#user_chat').empty();
                    (response.chat.messages || []).forEach(function (message) { chat.append(messageNode(message, window.currentUserId || 0)); });
                }
            })
            .always(function () { syncing = false; });
    }

    syncMessages();
    window.setInterval(syncMessages, 5000);
})(window.jQuery);
