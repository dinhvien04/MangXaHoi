<div class="hb-state-page">
    <section class="hb-card hb-state-card">
        <span class="hb-state-icon hb-state-icon-danger"><i class="bi bi-slash-circle"></i></span>
        <span class="hb-state-eyebrow">HANDBOOK SOCIAL</span>
        <h1>Tài khoản của bạn đã bị chặn</h1>
        <p>Xin chào <?= e($user['first_name'] . ' ' . $user['last_name']) ?>. Bạn hiện không thể sử dụng các tính năng của Handbook Social.</p>
        <div class="hb-state-note"><strong>Trạng thái tài khoản: Đã chặn</strong><span>Nếu bạn cho rằng đây là nhầm lẫn, hãy liên hệ quản trị viên.</span></div>
        <form method="post" action="?action=logout"><?= csrfField() ?><button type="submit" class="hb-danger-button"><i class="bi bi-box-arrow-right"></i> Đăng xuất khỏi tài khoản</button></form>
    </section>
</div>
