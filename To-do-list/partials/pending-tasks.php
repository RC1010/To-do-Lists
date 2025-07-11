
<h3><strong>To-do</strong></h3>
<div class="text-dark me-3">
    <p><span id="realTimeDate"></span> Today</p>
</div>
<?php if (!empty($pendingTasks)): ?>
    <ul class="list-group mb-3">
        <?php foreach ($pendingTasks as $task): ?>
            <?php
                $due_date = new DateTime($task['due_date']);
                $today = new DateTime();
                $interval = $today->diff($due_date);
                $days_left = (int)$interval->format('%r%a');

                if ($days_left < 0) {
                    $due_label = "<span class='badge bg-danger'>Overdue</span>";
                } elseif ($days_left === 0) {
                    $due_label = "<span class='badge bg-warning text-dark'>Due Today</span>";
                } elseif ($days_left === 1) {
                    $due_label = "<span class='badge bg-info text-dark'>Due Tomorrow</span>";
                } else {
                    $due_label = "<span class='badge bg-secondary'>In $days_left days</span>";
                }

                $priority = $task['priority'];
                $priority_badge = match($priority) {
                    'High' => "<span class='badge bg-danger'>High</span>",
                    'Medium' => "<span class='badge bg-warning text-dark'>Medium</span>",
                    'Low' => "<span class='badge bg-success'>Low</span>",
                    default => '',
                };

                $desc = htmlspecialchars($task['description']);
                $shortDesc = mb_substr($desc, 0, 5) . (mb_strlen($desc) > 5 ? '...' : '');
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= htmlspecialchars($task['title']) ?></strong><br>
                    <small class="text-muted" title="<?= $desc ?>"><?= $shortDesc ?></small><br>
                    <?= $priority_badge ?>
                </div>
                <div><?= $due_label ?></div>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Pagination for Pending Tasks -->
    <?php if ($totalPendingPages > 1): ?>
        <nav>
            <ul class="pagination pending-pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?= $pendingPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pending_page=<?= max(1, $pendingPage - 1) ?>#pendingTasks">Previous</a>
                </li>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $totalPendingPages; $i++): ?>
                    <li class="page-item <?= $i === $pendingPage ? 'active' : '' ?>">
                        <a class="page-link" href="?pending_page=<?= $i ?>#pendingTasks"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?= $pendingPage >= $totalPendingPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pending_page=<?= min($totalPendingPages, $pendingPage + 1) ?>#pendingTasks">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

<?php else: ?>
    <p>No pending tasks.</p>
<?php endif; ?>

</div>