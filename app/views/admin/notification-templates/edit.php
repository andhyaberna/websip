<?php $title = 'Edit Template: ' . htmlspecialchars($template['name']); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Notification Template</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/notification-templates" class="btn btn-sm btn-outline-secondary">
            &larr; Back to List
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/admin/notification-templates/<?= $template['id'] ?>/update" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Template Code</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($template['code']) ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($template['name']) ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Type</label>
                        <div>
                            <span class="badge bg-<?= $template['type'] == 'email' ? 'primary' : 'success' ?>">
                                <?= ucfirst($template['type']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <p class="form-text"><?= htmlspecialchars($template['description']) ?></p>
                    </div>

                    <?php if ($template['type'] == 'email'): ?>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?= htmlspecialchars($template['subject']) ?>" required>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="body" class="form-label">Body Message</label>
                        <textarea class="form-control font-monospace" id="body" name="body" rows="10" required><?= htmlspecialchars($template['body']) ?></textarea>
                        <div class="form-text">
                            You can use the variables listed on the right side.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="/admin/notification-templates" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Available Variables</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Click to copy variable to clipboard.</p>
                <?php 
                $vars = array_map('trim', explode(',', $template['variables'] ?? ''));
                if (!empty($vars) && $vars[0] != ''): 
                ?>
                    <div class="list-group">
                        <?php foreach ($vars as $var): ?>
                            <button type="button" class="list-group-item list-group-item-action py-2 font-monospace copy-var" data-var="<?= htmlspecialchars($var) ?>">
                                <?= htmlspecialchars($var) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small">No variables defined for this template.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.copy-var').forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.getAttribute('data-var');
        navigator.clipboard.writeText(text).then(() => {
            // Optional: Show tooltip or feedback
            const originalText = this.innerHTML;
            this.innerHTML = 'Copied!';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 1000);
        });
    });
});
</script>
