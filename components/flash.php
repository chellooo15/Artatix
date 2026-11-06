<?php if (!empty($flash)): ?>
    <div class="flash-container" id="flashContainer">
        <?php foreach ($flash as $type => $message): ?>
            <div class="flash flash-<?= $type ?>" role="alert">
                <span class="flash-message"><?= e($message) ?></span>
                <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>