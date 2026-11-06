<div class="container" style="max-width: 700px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/ticket-classes') ?>" style="color: inherit;">Kelas Tiket</a> / 
        <span>Edit: <?= h($ticketClass->name) ?></span>
    </nav>
    
    <div class="card">
        <div class="card-header"><h2>Edit Kelas Tiket</h2></div>
        <div class="card-body">
            <form action="<?= url('/admin/ticket-classes/' . $ticketClass->id) ?>" method="POST" data-validate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="event_id">Acara <span class="text-danger">*</span></label>
                    <select id="event_id" name="event_id" class="form-select" required>
                        <option value="">-- Pilih Acara --</option>
                        <?php $selectedEventId = $ticketClass->event_id; require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="name">Nama Kelas Tiket <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required 
                           value="<?= h($ticketClass->name) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="class_type">Tipe Kelas <span class="text-danger">*</span></label>
                    <select id="class_type" name="class_type" class="form-select" required>
                        <option value="cat" <?= $ticketClass->class_type === 'cat' ? 'selected' : '' ?>>CAT (Duduk)</option>
                        <option value="standing" <?= $ticketClass->class_type === 'standing' ? 'selected' : '' ?>>Festival / Standing</option>
                        <option value="vip" <?= $ticketClass->class_type === 'vip' ? 'selected' : '' ?>>VIP</option>
                        <option value="vvip" <?= $ticketClass->class_type === 'vvip' ? 'selected' : '' ?>>VVIP</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="section">Seksi / Blok / Area</label>
                    <input type="text" id="section" name="section" class="form-input" 
                           value="<?= h($ticketClass->section ?? '') ?>">
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="price">Harga <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" class="form-input" required min="0" step="1000" 
                               value="<?= $ticketClass->price ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="capacity">Kapasitas <span class="text-danger">*</span></label>
                        <input type="number" id="capacity" name="capacity" class="form-input" required min="1" 
                               value="<?= $ticketClass->capacity ?>">
                    </div>
                </div>

                <?php $seatMode = $ticketClass->seat_mode ?? 'quantity'; $seatLayout = $ticketClass->seatLayout(); $seatSoldCount = (int)($ticketClass->sold_count ?? 0); require __DIR__ . '/../../../components/seat_builder.php'; ?>
                
                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-textarea" rows="3"><?= h($ticketClass->description ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="benefits">Benefit / Fasilitas (satu per baris)</label>
                    <textarea id="benefits" name="benefits" class="form-textarea" rows="4"><?= h($ticketClass->benefits ?? '') ?></textarea>
                    <p class="form-help">Pisahkan setiap benefit dengan baris baru (Enter)</p>
                </div>
                
                <div class="form-group d-flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= $ticketClass->is_active ? 'checked' : '' ?> style="width: auto; margin-right: 0.5rem;">
                    <label for="is_active" class="form-label" style="margin: 0; font-weight: 400;">Aktifkan kelas tiket ini</label>
                </div>
                
                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/ticket-classes') ?>" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="d-flex gap-2 mt-4">
        <form action="<?= url('/admin/ticket-classes/' . $ticketClass->id . '/delete') ?>" method="POST" onsubmit="return confirm('Hapus kelas tiket ini? Tindakan ini tidak bisa dibatalkan.')">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-danger">Hapus Kelas Tiket</button>
        </form>
        <a href="<?= url('/admin/ticket-classes') ?>" class="btn btn-outline">Kembali ke Daftar</a>
    </div>
</div>