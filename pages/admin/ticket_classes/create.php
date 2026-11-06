<div class="container" style="max-width: 700px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/ticket-classes') ?>" style="color: inherit;">Kelas Tiket</a> / 
        <span>Tambah Baru</span>
    </nav>
    
    <div class="card">
        <div class="card-header"><h2>Tambah Kelas Tiket</h2></div>
        <div class="card-body">
            <form action="<?= url('/admin/ticket-classes') ?>" method="POST" data-validate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="event_id">Acara <span class="text-danger">*</span></label>
                    <select id="event_id" name="event_id" class="form-select" required>
                        <option value="">-- Pilih Acara --</option>
                        <?php require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="name">Nama Kelas Tiket <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required 
                           value="<?= old('name') ?>" placeholder="Contoh: CAT Premium, VIP Early Bird, Standing Regular">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="class_type">Tipe Kelas <span class="text-danger">*</span></label>
                    <select id="class_type" name="class_type" class="form-select" required>
                        <option value="cat" <?= old('class_type') === 'cat' ? 'selected' : '' ?>>CAT (Duduk)</option>
                        <option value="standing" <?= old('class_type') === 'standing' ? 'selected' : '' ?>>Festival / Standing</option>
                        <option value="vip" <?= old('class_type') === 'vip' ? 'selected' : '' ?>>VIP</option>
                        <option value="vvip" <?= old('class_type') === 'vvip' ? 'selected' : '' ?>>VVIP</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="section">Seksi / Blok / Area</label>
                    <input type="text" id="section" name="section" class="form-input" 
                           value="<?= old('section') ?>" placeholder="Contoh: Blok A, VIP Area, Golden Circle">
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="price">Harga <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" class="form-input" required min="0" step="1000" 
                               value="<?= old('price') ?>" placeholder="Contoh: 1500000">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="capacity">Kapasitas <span class="text-danger">*</span></label>
                        <input type="number" id="capacity" name="capacity" class="form-input" required min="1" 
                               value="<?= old('capacity') ?>" placeholder="Contoh: 100">
                    </div>
                </div>

                <?php $seatMode = old('seat_mode') ?: 'quantity'; $seatLayout = ['rows' => old('seat_rows') ?? '', 'cols' => old('seat_cols') ?? '', 'blocked' => old('seat_blocked') ?? '', 'prefix' => old('seat_prefix') ?? '']; $seatSoldCount = 0; require __DIR__ . '/../../../components/seat_builder.php'; ?>
                
                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-textarea" rows="3" 
                              placeholder="Deskripsi singkat kelas tiket ini"><?= old('description') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="benefits">Benefit / Fasilitas (satu per baris)</label>
                    <textarea id="benefits" name="benefits" class="form-textarea" rows="4" 
                              placeholder="Contoh:
Akses masuk prioritas
Makanan & minuman gratis
Merchandise eksklusif
Meet & Greet (untuk VVIP)"><?= old('benefits') ?></textarea>
                    <p class="form-help">Pisahkan setiap benefit dengan baris baru (Enter)</p>
                </div>
                
                <div class="form-group d-flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= !isset($_POST['is_active']) || old('is_active') ? 'checked' : '' ?> style="width: auto; margin-right: 0.5rem;">
                    <label for="is_active" class="form-label" style="margin: 0; font-weight: 400;">Aktifkan kelas tiket ini</label>
                </div>
                
                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/ticket-classes') ?>" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Kelas Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>