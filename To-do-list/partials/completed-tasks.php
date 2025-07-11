<h3><strong>Completed Tasks</strong></h3>
<?php if (!empty($completedTasks)): ?>
    <ul class="list-group mb-3 my-3">
        <?php foreach ($completedTasks as $task): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                    <?php
                    $desc = htmlspecialchars($task['description']); // full description
                    $shortDesc = mb_substr($desc, 0, 5) . (mb_strlen($desc) > 5 ? '...' : '');
                    ?>
                    <strong><?= htmlspecialchars($task['title']) ?></strong><br>
                    <small class="text-muted" title="<?= $desc ?>"><?= $shortDesc ?></small>
                </div>
                <span class="badge bg-success"><?= date('M d, Y', strtotime($task['updated_at'])) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Pagination Controls -->
    <?php if ($totalCompletedPages > 1): ?>
        <nav>
            <ul class="pagination completed-pagination justify-content-center">
                <li class="page-item <?= $completedPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?completed_page=<?= $completedPage - 1 ?>#completedTasks">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalCompletedPages; $i++): ?>
                    <li class="page-item <?= $i === $completedPage ? 'active' : '' ?>">
                        <a class="page-link" href="?completed_page=<?= $i ?>#completedTasks"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $completedPage >= $totalCompletedPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?completed_page=<?= $completedPage + 1 ?>#completedTasks">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

<?php else: ?>
    <p>No completed tasks yet.</p>
<?php endif; ?>
