<?php
require_once __DIR__ . '/Admin_filters.php';
function render_pagination($page, $total_page, $block_start, $block_end, $total) {
    // Apache rewrite가 내부 전달한 url은 제외하고 검색 조건은 유지한다.
    $query = $_GET;
    $route = (string) ($query['url'] ?? trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/'));
    $kind = explode('/', trim($route, '/'))[1] ?? '';
    if (!Admin_filters::uses_dates($kind)) $query = array_intersect_key($query, array_flip(['page','per_page','q']));
    unset($query['url'], $query['page']);
    $last_page = max(1, (int) $total_page);
    $page_link = function ($target) use ($query, $last_page) {
        $params = $query;
        $params['page'] = max(1, min($last_page, (int) $target));
        return htmlspecialchars('?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986), ENT_QUOTES, 'UTF-8');
    };
    ob_start();
    ?>
    <div class="admin-pagination">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page_link(1) ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?= $block_start <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page_link($block_start - 1) ?>" aria-label="Previous" title="이전 페이지 묶음">
                        <span aria-hidden="true">&lt;</span>
                    </a>
                </li>

                <?php if ($total > 0): ?>
                    <?php for ($i = $block_start; $i <= $block_end; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $page_link($i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                <?php else: ?>
                    <li class="page-item active"><a class="page-link" href="<?= $page_link(1) ?>">1</a></li>
                <?php endif; ?>

                <li class="page-item <?= $block_end >= $last_page ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page_link($block_end + 1) ?>" aria-label="Next" title="다음 페이지 묶음">
                        <span aria-hidden="true">&gt;</span>
                    </a>
                </li>
                <li class="page-item <?= $page >= $total_page ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $page_link($total_page) ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}
