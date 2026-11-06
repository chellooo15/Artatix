<div class="container" style="max-width: 700px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/venues') ?>" style="color: inherit;">Venue</a> / 
        <span>Edit: <?= h($venue->name) ?></span>
    </nav>
    
    <div class="card">
        <div class="card-header"><h2>Edit Venue</h2></div>
        <div class="card-body">
            <form action="<?= url('/admin/venues/' . $venue->id) ?>" method="POST" enctype="multipart/form-data" data-validate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Nama Venue <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required 
                           value="<?= h($venue->name) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" class="form-textarea" rows="3"><?= h($venue->address ?? '') ?></textarea>
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="city">Kota <span class="text-danger">*</span></label>
                        <input type="text" id="city" name="city" class="form-input" required 
                               value="<?= h($venue->city) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="capacity">Kapasitas Maksimal</label>
                        <input type="number" id="capacity" name="capacity" class="form-input" min="0" 
                               value="<?= $venue->capacity ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gambar Saat Ini</label>
                    <?php if ($venue->image): ?>
                        <div class="d-flex items-center gap-3 mb-2">
                            <img src="<?= asset('assets/uploads/' . $venue->image) ?>" alt="" style="width: 120px; height: 68px; object-fit: cover; border-radius: 4px;">
                            <div>
                                <div class="text-sm"><?= h($venue->image) ?></div>
                                <label class="form-label" style="margin: 0; font-weight: 400; font-size: 0.8125rem;">
                                    <input type="checkbox" name="remove_image" value="1"> Hapus gambar
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">Belum ada gambar</span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="image">Ganti Gambar (Opsional)</label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/jpeg,image/png,image/webp">
                    <p class="form-help">Format: JPG, PNG, WebP. Maksimal 5MB. Akan mengganti gambar lama.</p>
                </div>
                
                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/venues') ?>" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="d-flex gap-2 mt-4">
        <form action="<?= url('/admin/venues/' . $venue->id . '/delete') ?>" method="POST" onsubmit="return confirm('PERHATIAN: Menghapus venue yang digunakan acara tidak diizinkan. Lanjutkan?')">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-danger">Hapus Venue</button>
        </form>
        <a href="<?= url('/admin/venues') ?>" class="btn btn-outline">Kembali ke Daftar</a>
    </div>
</div>