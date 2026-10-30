/* Shared admin POST navigation and presentation enhancements. */
window.adminPost = function (url, data) {
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token || !token.content) { alert('페이지를 새로고침한 뒤 다시 시도해 주세요.'); return; }
    var form = document.createElement('form');
    form.method = 'post'; form.action = url; form.hidden = true;
    var fields = Object.assign({}, data || {}, {csrf: token.content});
    Object.keys(fields).forEach(function (name) {
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = fields[name];
        form.appendChild(input);
    });
    document.body.appendChild(form); form.submit();
};
document.addEventListener('DOMContentLoaded', function () {
    document.documentElement.classList.add('admin-page');
    document.querySelectorAll('.nav-right > .table, .admin-row > .table').forEach(function (table) {
        var scroll = document.createElement('div');
        scroll.className = 'admin-table-scroll';
        scroll.tabIndex = 0;
        scroll.setAttribute('role', 'region');
        scroll.setAttribute('aria-label', '관리 목록 (좌우 스크롤)');
        table.before(scroll);
        scroll.appendChild(table);
    });
    var title = document.querySelector('.main-title');
    if (title) {
        var eyebrow = document.createElement('p');
        eyebrow.className = 'admin-eyebrow';
        eyebrow.textContent = '하늘문 · 예배 관리';
        title.before(eyebrow);
    }
    document.querySelectorAll('.nav-menu > .text-center').forEach(function (brand) {
        var caption = document.createElement('span');
        caption.className = 'admin-brand-caption';
        caption.textContent = 'ADMIN CONSOLE';
        brand.appendChild(caption);
    });
    var route = window.location.pathname.replace(/\/$/, '') || '/admin/main';
    var parents = { '/admin/live_read': '/admin/live_list', '/admin/video_read': '/admin/video_list', '/admin/admin_read': '/admin/admin_list', '/admin/preacher_read': '/admin/preacher_list', '/admin/saint_read': '/admin/saint_list' };
    route = parents[route] || (route === '/admin' ? '/admin/main' : route);
    document.querySelectorAll('.nav-side').forEach(function (link) {
        if (link.getAttribute('href') === route) link.setAttribute('aria-current', 'page');
    });
});
