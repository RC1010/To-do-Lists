<div class="modal fade" id="editCategoryModal_<?= $category['id'] ?>" tabindex="-1"
    aria-labelledby="editCategoryModalLabel_<?= $category['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="./database/edit-category.php" method="POST">
                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel_<?= $category['id'] ?>">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name_<?= $category['id'] ?>" class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="category_name"
                        id="category_name_<?= $category['id'] ?>" value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description_<?= $category['id'] ?>" class="form-label">Description</label>
                        <textarea class="form-control" name="category_description"
                        id="category_description_<?= $category['id'] ?>"><?= htmlspecialchars($category['description']) ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label for="category_name" class="form-label"><strong>Category Name</strong></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-3">
                        <label for="category_description" class="form-label"><strong>Description</strong></label>
                        <textarea class="form-control" id="category_description" name="category_description" rows="3" placeholder="Enter category description"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
            
        </div>
    </div>
</div>