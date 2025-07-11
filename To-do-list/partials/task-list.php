<div class="container" id="container2">
    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Title</th>
                    <th class="p-2">Description</th>
                    <th class="p-2">Category</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Due Date</th>
                    <th class="p-2">Created At</th>
                    <th class="p-2">Updated At</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (!empty($tasks)) {
                        $count = $offset + 1; // Initialize task counter
                        foreach ($tasks as $task):
                            $fullTitle = htmlspecialchars($task['title']);
                            $shortTitle = mb_substr($fullTitle, 0, 5) . (mb_strlen($fullTitle) > 5 ? '...' : '');
                            // Calculate description variables ONCE per task
                            $desc = htmlspecialchars($task['description']);
                            // Use mb_substr and mb_strlen for proper multi-byte character handling
                            $shortDesc = mb_substr($desc, 0, 5) . (mb_strlen($desc) > 5 ? '...' : '');
                            $cat = htmlspecialchars($task['category_name'] ?? 'Uncategorized');
                            $shortCat = mb_substr($cat, 0, 5) . (mb_strlen($cat) > 5 ? '...' : '');
                ?>
                    <tr>
                        <td class="p-2"><?= $count ?></td>
                        <td class="p-2" title="<?= $fullTitle ?>"><?= $shortTitle ?></td>
                        <td class="p-2" title="<?= $desc ?>"><?= $shortDesc ?></td>
                        <td title="<?= $cat ?>"><?= $shortCat ?></td>
                        <td class="p-2"><?= htmlspecialchars($task['status']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($task['due_date']) ?></td>
                        <td class="p-2"><?= $task['created_at'] ?></td>
                        <td class="p-2"><?= $task['updated_at'] ?></td>
                        <td class="p-2">
                            <button type="button" class="btn btn-success btn-sm me-1" data-bs-toggle="modal" data-bs-target="#editTaskModal_<?= $task['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteTask(<?= $task['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    
                <?php include './modals/edit-task-modal.php'; ?>

                <?php
                            $count++; // Increment count for the next row
                        endforeach;
                    } else {
                        echo "<tr><td colspan='9' class='text-center p-3'>No tasks available</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
    <!-- Task Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center pending-pagination">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>&cat_page=<?= $categoryPage ?>#taskList">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link pagination-link" href="?page=<?= $i ?>&cat_page=<?= $categoryPage ?>#taskList"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>&cat_page=<?= $categoryPage ?>#taskList">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
