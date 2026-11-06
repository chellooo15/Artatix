<div class="container" style="max-width: 700px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/po-codes') ?>" style="color: inherit;">Kode Flash Sale</a> / 
        <span>Buat Baru</span>
    </nav>
    
    <div class="card">
        <div class="card-header"><h2>Buat Kode Flash Sale</h2></div>
        <div class="card-body">
            <form action="<?= url('/admin/po-codes') ?>" method="POST" data-validate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="event_id">Acara <span class="text-danger">*</span></label>
                    <select id="event_id" name="event_id" class="form-select" required>
                        <option value="">-- Pilih Acara --</option>
                        <?php require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                    <p class="form-help">Hanya acara dengan status Draft atau Flash Sale yang ditampilkan.</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="code">Kode Flash Sale <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="code" name="code" class="form-input" required 
                               value="<?= strtoupper(old('code') ?? generatePOCode()) ?>" placeholder="Contoh: FS-ABCD12" style="text-transform: uppercase;">
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('code').value = 'FS-' + Math.random().toString(36).substring(2, 8).toUpperCase()">Generate</button>
                    </div>
                    <p class="form-help">Kode unik untuk akses Flash Sale. Bisa digenerate otomatis atau input manual.</p>
                </div>
                
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="max_quota">Kuota Maksimal <span class="text-danger">*</span></label>
                        <input type="number" id="max_quota" name="max_quota" class="form-input" required min="1" 
                               value="<?= old('max_quota') ?>" placeholder="Contoh: 100">
                        <p class="form-help">Jumlah maksimal tiket yang bisa dibeli dengan kode ini</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="expires_at">Berlaku Sampai</label>
                        <input type="datetime-local" id="expires_at" name="expires_at" class="form-input" 
                               value="<?= old('expires_at') ? date('Y-m-d\TH:i', strtotime(old('expires_at'))) : '' ?>">
                        <p class="form-help">Kosongkan = tidak kadaluarsa (terbatas oleh kuota saja)</p>
                    </div>
                </div>
                
                <div class="form-group d-flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked style="width: auto; margin-right: 0.5rem;">
                    <label for="is_active" class="form-label" style="margin: 0; font-weight: 400;">Aktifkan kode Flash Sale ini</label>
                </div>
                
                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/po-codes') ?>" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Buat Kode Flash Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>