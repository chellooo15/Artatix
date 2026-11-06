<div class="container" style="max-width: 800px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/events') ?>" style="color: inherit;">Acara</a> / 
        <span>Tambah Baru</span>
    </nav>
    
    <div class="card">
        <div class="card-header"><h2>Buat Acara Baru</h2></div>
        <div class="card-body">
            <form action="<?= url('/admin/events') ?>" method="POST" enctype="multipart/form-data" data-validate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Nama Acara <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required 
                           value="<?= old('name') ?>" placeholder="Contoh: Konser Superstar 2024">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-textarea" rows="4" 
                              placeholder="Deskripsi acara, lineup artis, dll."><?= old('description') ?></textarea>
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="event_date">Tanggal Acara <span class="text-danger">*</span></label>
                        <input type="date" id="event_date" name="event_date" class="form-input" required 
                               value="<?= old('event_date') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="event_time">Waktu Acara <span class="text-danger">*</span></label>
                        <input type="time" id="event_time" name="event_time" class="form-input" required 
                               value="<?= old('event_time') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="venue_id">Venue</label>
                    <select id="venue_id" name="venue_id" class="form-select">
                        <option value="">-- Pilih Venue --</option>
                        <?php foreach ($venues as $v): ?>
                            <option value="<?= $v->id ?>" <?= old('venue_id') == $v->id ? 'selected' : '' ?>>
                                <?= h($v->name) ?> - <?= h($v->city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-help">Belum ada venue? <a href="<?= url('/admin/venues/create') ?>" target="_blank">Tambah venue baru</a></p>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="banner_image">Banner Acara</label>
                    <input type="file" id="banner_image" name="banner_image" class="form-input" accept="image/jpeg,image/png,image/webp">
                    <p class="form-help">Format: JPG, PNG, WebP. Maksimal 5MB. Rasio disarankan 16:9.</p>
                </div>
                
                <hr style="margin: 1.5rem 0; border-color: var(--color-border);">
                <h4 class="mb-4">Pengaturan Flash Sale (Opsional)</h4>
                
                <div class="form-group">
                    <label class="form-label" for="status">Status Acara</label>
                    <select id="status" name="status" class="form-select">
                        <option value="draft" <?= (old('status') ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="po_open" <?= old('status') === 'po_open' ? 'selected' : '' ?>>Flash Sale Dibuka</option>
                        <option value="public" <?= old('status') === 'public' ? 'selected' : '' ?>>For Sale</option>
                        <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="po_quota">Kuota Flash Sale</label>
                        <input type="number" id="po_quota" name="po_quota" class="form-input" min="0" 
                               value="<?= old('po_quota') ?>" placeholder="0 = tidak ada Flash Sale">
                        <p class="form-help">Jumlah tiket yang dialokasikan untuk Flash Sale</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="po_open_at">Flash Sale Dibuka</label>
                        <input type="datetime-local" id="po_open_at" name="po_open_at" class="form-input" 
                               value="<?= old('po_open_at') ? date('Y-m-d\TH:i', strtotime(old('po_open_at'))) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="public_open_at">Mulai For Sale</label>
                    <input type="datetime-local" id="public_open_at" name="public_open_at" class="form-input" 
                           value="<?= old('public_open_at') ? date('Y-m-d\TH:i', strtotime(old('public_open_at'))) : '' ?>">
                    <p class="form-help">Kosongkan = langsung terbuka setelah Flash Sale selesai/dibatalkan</p>
                </div>
                
                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/events') ?>" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Acara</button>
                </div>
            </form>
        </div>
    </div>
</div>