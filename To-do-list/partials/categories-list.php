<!-- Category Table -->
<div class="container" id="container2">
    <table class="table table-bordered table-hover text-center">
        <thead class="table-dark">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Category Name</th>
                <th class="p-2">Description</th>
                <th class="p-2">Created At</th>
                <th class="p-2">Updated At</th>
                <th class="p-2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)):
                $count = $categoryOffset + 1; // Initialize category counter
                foreach ($categories as $category):
                    // Calculate description variables ONCE per category
                    $desc = htmlspecialchars($category['description']);
                    // Use mb_substr and mb_strlen for proper multi-byte character handling
                    $shortDesc = shortText($desc);
            ?>
                    <tr>
                        <td class="p-2"><?= $count ?></td>
                        <td class="p-2"><?= htmlspecialchars($category['name']) ?></td>
                        <td class="p-2" title="<?= $desc ?>"><?= $shortDesc ?></td>
                        <td class="p-2"><?= $category['created_at'] ?? 'N/A' ?></td>
                        <td class="p-2"><?= $category['updated_at'] ?? 'N/A' ?></td>
                        <td class="p-2">
                            <button type="button" class="btn btn-success btn-sm me-1"
                                data-bs-toggle="modal" data-bs-target="#editCategoryModal_<?= $category['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button class="btn btn-danger btn-sm" onclick="deleteCategory(<?= $category['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <?php include './modals/edit-category-modal.php'; ?>

            <?php
                    $count++; // Increment count for the next row
                endforeach;
            else: ?>
                <tr><td colspan="6" class="text-center p-3">No categories available</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($totalCategoryPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center completed-pagination">
                <!-- Previous Button -->
                <li class="page-item <?= $categoryPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage - 1 ?>#catList">Previous</a>
                </li>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $totalCategoryPages; $i++): ?>
                    <li class="page-item <?= $i == $categoryPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $i ?>#catList"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Button -->
                <li class="page-item <?= $categoryPage >= $totalCategoryPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage ?>&cat_page=<?= $categoryPage + 1 ?>#catList">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
