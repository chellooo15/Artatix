<div class="container">
    <div class="d-flex justify-between items-center flex-wrap gap-3 mb-6">
        <h1>Semua Acara</h1>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/events') ?>" method="GET" class="grid grid-4 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="search">Cari Acara</label>
                    <input type="text" id="search" name="search" class="form-input" 
                           placeholder="Nama artis, event..." value="<?= h($filters['search'] ?? '') ?>">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="po_open" <?= ($filters['status'] ?? '') === 'po_open' ? 'selected' : '' ?>>Flash Sale</option>
                        <option value="public" <?= ($filters['status'] ?? '') === 'public' ? 'selected' : '' ?>>For Sale</option>
                        <option value="sold_out" <?= ($filters['status'] ?? '') === 'sold_out' ? 'selected' : '' ?>>Sold Out</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="venue">Venue</label>
                    <select id="venue" name="venue" class="form-select">
                        <option value="">Semua Venue</option>
                        <?php foreach ($venues as $v): ?>
                            <option value="<?= $v->id ?>" <?= (int)($filters['venueId'] ?? 0) === (int)$v->id ? 'selected' : '' ?>>
                                <?= h($v->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= url('/events') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($events)): ?>
        <div class="card empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-text-muted);">
                <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3>Tidak Ada Acara</h3>
            <p class="text-muted">Coba ubah filter pencarian atau cek kembali nanti.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3 mb-6" id="eventsGrid">
            <?php foreach ($events as $event): ?>
                <?php require __DIR__ . '/../../components/event_card.php'; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if ($pagination['last_page'] > 1): ?>
            <nav class="d-flex justify-center gap-2 flex-wrap" aria-label="Pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="<?= url('/events?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-outline">Sebelumnya</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                    <a href="<?= url('/events?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                       class="btn btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?> btn-sm">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="<?= url('/events?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-outline">Selanjutnya</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>