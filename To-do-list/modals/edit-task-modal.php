<div class="modal fade" id="editTaskModal_<?= $task['id'] ?>" tabindex="-1"
    aria-labelledby="editTaskModalLabel_<?= $task['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="./database/edit-task.php" method="POST">
                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel_<?= $task['id'] ?>">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Category -->
                        <div class="col-md-6 mb-3">
                            <label for="task_category_<?= $task['id'] ?>" class="form-label">Category</label>
                            <select class="form-select" name="task_category_id" id="task_category_<?= $task['id'] ?>">
                                <?php
                                // Ensure $categories is available and is an array/iterable
                                if (isset($categories) && is_array($categories)) {
                                    foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= $task['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach;
                                } else {
                                    // Fallback or error message if categories are not loaded
                                    echo "<option value=''>No categories available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Priority -->
                        <div class="col-md-6 mb-3">
                            <label for="task_priority_<?= $task['id'] ?>" class="form-label">Priority</label>
                            <select class="form-select" name="task_priority" id="task_priority_<?= $task['id'] ?>">
                                <?php
                                $priorities = ['low', 'medium', 'high'];
                                foreach ($priorities as $priority) {
                                    $selected = $task['priority'] === $priority ? 'selected' : '';
                                    echo "<option value='$priority' $selected>$priority</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="task_status_<?= $task['id'] ?>" class="form-label">Status</label>
                            <select class="form-select" name="task_status" id="task_status_<?= $task['id'] ?>">
                                <?php
                                $statuses = ['Not Started', 'In Progress', 'Completed'];
                                foreach ($statuses as $status) {
                                    $selected = $task['status'] === $status ? 'selected' : '';
                                    echo "<option value='$status' $selected>$status</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Due Date -->
                        <div class="col-md-6 mb-3">
                            <label for="due_date_<?= $task['id'] ?>" class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="due_date_<?= $task['id'] ?>"
                                value="<?= htmlspecialchars(date('Y-m-d', strtotime($task['due_date']))) ?>">
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="task_title_<?= $task['id'] ?>" class="form-label">Title</label>
                        <input type="text" class="form-control" name="task_title"
                            id="task_title_<?= $task['id'] ?>" value="<?= htmlspecialchars($task['title']) ?>" required>
                    </div>
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="task_description_<?= $task['id'] ?>" class="form-label">Description</label>
                        <textarea class="form-control" name="task_description"
                            id="task_description_<?= $task['id'] ?>" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>