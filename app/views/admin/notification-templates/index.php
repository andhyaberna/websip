<?php $title = 'Notification Templates'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Notification Templates</h1>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Subject</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $template): ?>
                <tr>
                    <td><?= htmlspecialchars($template['code']) ?></td>
                    <td><?= htmlspecialchars($template['name']) ?></td>
                    <td>
                        <span class="badge bg-<?= $template['type'] == 'email' ? 'primary' : 'success' ?>">
                            <?= ucfirst($template['type']) ?>
                        </span>
                    </td>
                    <td><?= $template['type'] == 'email' ? htmlspecialchars($template['subject']) : '-' ?></td>
                    <td>
                        <a href="/admin/notification-templates/<?= $template['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
