<?php
function render_pagination($page, $total_page, $block_start, $block_end, $total) {
    ob_start();
    ?>
    <div class="d-grid d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1" aria-label="First">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&lt;</span>
                    </a>
                </li>

                <?php if ($total > 0): ?>
                    <?php for ($i = $block_start; $i <= $block_end; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                <?php else: ?>
                    <li class="page-item active"><a class="page-link" href="?page=1">1</a></li>
                <?php endif; ?>

                <li class="page-item <?= $page >= $total_page ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&gt;</span>
                    </a>
                </li>
                <li class="page-item <?= $page >= $total_page ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $total_page ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}
