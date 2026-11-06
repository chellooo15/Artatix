<div class="container" style="max-width: 700px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/venues') ?>" style="color: inherit;">Venue</a> / 
        <span>Tambah Baru</span>
    </nav>
    
    <div class="card">
        <div class="card-header"><h2>Tambah Venue</h2></div>
        <div class="card-body">
            <form action="<?= url('/admin/venues') ?>" method="POST" enctype="multipart/form-data" data-validate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Nama Venue <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required 
                           value="<?= old('name') ?>" placeholder="Contoh: Gelora Bung Karno, Istora Senayan">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" class="form-textarea" rows="3" 
                              placeholder="Alamat lengkap venue"><?= old('address') ?></textarea>
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="city">Kota <span class="text-danger">*</span></label>
                        <input type="text" id="city" name="city" class="form-input" required 
                               value="<?= old('city') ?>" placeholder="Contoh: Jakarta">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="capacity">Kapasitas Maksimal</label>
                        <input type="number" id="capacity" name="capacity" class="form-input" min="0" 
                               value="<?= old('capacity') ?>" placeholder="Contoh: 50000">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="image">Gambar Venue</label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/jpeg,image/png,image/webp">
                    <p class="form-help">Format: JPG, PNG, WebP. Maksimal 5MB.</p>
                </div>
                
                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/venues') ?>" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Venue</button>
                </div>
            </form>
        </div>
    </div>
</div>