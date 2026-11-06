<?php
// Shared seat-map builder for ticket class forms.
// Optional vars: $seatMode ('quantity'|'seated'), $seatLayout (array|string rows), $seatSoldCount (int).
$seatMode = $seatMode ?? 'quantity';
$layout = $seatLayout ?? [];
$rowsRaw = $layout['rows'] ?? '';
$rowsStr = is_array($rowsRaw) ? implode(',', $rowsRaw) : (string)$rowsRaw;
$blockedRaw = $layout['blocked'] ?? '';
$blockedStr = is_array($blockedRaw) ? implode(',', $blockedRaw) : (string)$blockedRaw;
$colsVal = $layout['cols'] ?? '';
$prefixVal = $layout['prefix'] ?? '';
$soldCount = (int)($seatSoldCount ?? 0);
?>
<div class="form-group">
    <label class="form-label" for="seat_mode">Mode Kursi</label>
    <select id="seat_mode" name="seat_mode" class="form-select">
        <option value="quantity" <?= $seatMode === 'quantity' ? 'selected' : '' ?>>Kuantitas (tanpa pilih kursi)</option>
        <option value="seated" <?= $seatMode === 'seated' ? 'selected' : '' ?>>Pilih kursi (ala XXI)</option>
    </select>
    <p class="form-help">Mode kursi: pembeli memilih kursi spesifik di denah.</p>
</div>

<div id="seatBuilder" style="<?= $seatMode === 'seated' ? '' : 'display:none;' ?>">
    <?php if ($soldCount > 0): ?>
        <div class="alert alert-warning" style="font-size: 0.8125rem;"><?= $soldCount ?> kursi sudah terjual — layout baru tidak boleh menghapus kursi yang sudah terjual.</div>
    <?php endif; ?>
    <div class="grid grid-2 gap-4">
        <div class="form-group">
            <label class="form-label" for="seat_rows">Baris (koma)</label>
            <input type="text" id="seat_rows" name="seat_rows" class="form-input"
                   value="<?= h($rowsStr) ?>" placeholder="A,B,C atau S untuk standing">
            <p class="form-help">Contoh duduk: A,B,C. Standing: S saja (jadi S1..Sn).</p>
        </div>
        <div class="form-group">
            <label class="form-label" for="seat_cols">Kursi per baris</label>
            <input type="number" id="seat_cols" name="seat_cols" class="form-input" min="1" max="100"
                   value="<?= h((string)$colsVal) ?>" placeholder="20">
        </div>
    </div>
    <div class="grid grid-2 gap-4">
        <div class="form-group">
            <label class="form-label" for="seat_prefix">Prefix (opsional)</label>
            <input type="text" id="seat_prefix" name="seat_prefix" class="form-input"
                   value="<?= h($prefixVal) ?>" placeholder="kosongkan saja">
        </div>
        <div class="form-group">
            <label class="form-label" for="seat_blocked">Blokir (koma)</label>
            <input type="text" id="seat_blocked" name="seat_blocked" class="form-input"
                   value="<?= h($blockedStr) ?>" placeholder="A1,B5">
            <p class="form-help">Kursi yang tidak dijual (tangga, pillar).</p>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Preview (<span id="seatTotal">0 kursi</span>)</label>
        <div id="seatPreview" class="seat-preview"></div>
        <p class="form-help">Kapasitas otomatis = jumlah kursi di atas.</p>
    </div>
</div>

<script>
(function(){
    const mode = document.getElementById('seat_mode');
    const builder = document.getElementById('seatBuilder');
    const rowsEl = document.getElementById('seat_rows');
    const colsEl = document.getElementById('seat_cols');
    const prefixEl = document.getElementById('seat_prefix');
    const blockedEl = document.getElementById('seat_blocked');
    const preview = document.getElementById('seatPreview');
    const total = document.getElementById('seatTotal');
    const cap = document.getElementById('capacity');
    if (!mode || !builder) return;
    function render(){
        const seated = mode.value === 'seated';
        builder.style.display = seated ? '' : 'none';
        if (cap) cap.readOnly = seated;
        if (!seated) return;
        const rows = (rowsEl.value || '').split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
        const cols = parseInt(colsEl.value) || 0;
        const prefix = (prefixEl.value || '').trim();
        const blocked = (blockedEl.value || '').split(/[\s,]+/).map(s => s.trim().toUpperCase()).filter(Boolean);
        let html = '', count = 0;
        rows.forEach(r => {
            html += '<div class="seat-row"><span class="seat-row-label">' + r + '</span>';
            for (let c = 1; c <= Math.min(cols, 100); c++) {
                const label = prefix + r + c;
                const isBlocked = blocked.includes(label);
                if (!isBlocked) count++;
                html += '<span class="seat-mini' + (isBlocked ? ' blocked' : '') + '" title="' + label + '"></span>';
            }
            html += '</div>';
        });
        preview.innerHTML = html;
        total.textContent = count + ' kursi';
        if (cap) cap.value = count;
    }
    [mode, rowsEl, colsEl, prefixEl, blockedEl].forEach(el => el && el.addEventListener('input', render));
    mode.addEventListener('change', render);
    render();
})();
</script>
