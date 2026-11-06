<div class="container">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/') ?>" style="color: inherit;">Beranda</a> / 
        <a href="<?= url('/events') ?>" style="color: inherit;">Acara</a> / 
        <span><?= h($event->name) ?></span>
    </nav>
    
    <div class="show-layout">
        <div>
            <?php $detailBanner = eventBannerUrl($event->banner_image ?? null); ?>
            <?php if ($detailBanner): ?>
                <div class="event-hero-media">
                    <img src="<?= $detailBanner ?>" alt="<?= h($event->name) ?>"
                         loading="lazy" width="1200" height="675" class="event-media-img"
                         onerror="this.closest('.event-hero-media').classList.add('event-placeholder'); this.remove();">
                </div>
            <?php else: ?>
                <div class="event-hero-media event-placeholder">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                </div>
            <?php endif; ?>
            
            <?php if ($event->description): ?>
                <div class="card mt-6">
                    <div class="card-header"><h3>Deskripsi Acara</h3></div>
                    <div class="card-body" style="white-space: pre-line;"><?= h($event->description) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($venue): ?>
                <div class="card mt-6">
                    <div class="card-header"><h3>Venue</h3></div>
                    <div class="card-body">
                        <p class="font-semibold"><?= h($venue->name) ?></p>
                        <p class="text-muted"><?= h($venue->address) ?>, <?= h($venue->city) ?></p>
                        <?php if ($venue->capacity): ?>
                            <p class="text-sm text-muted mt-2">Kapasitas: <?= number_format($venue->capacity) ?> orang</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="sticky-top" style="top: 90px;">
            <div class="card">
                <div class="card-header">
                    <h3>Pilih Tiket</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-between items-start mb-4">
                        <div>
                            <h2 style="font-size: 1.5rem; margin-bottom: 0.25rem;"><?= h($event->name) ?></h2>
                            <div class="d-flex gap-3 flex-wrap text-sm text-muted">
                                <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg><?= formatDate($event->event_date) ?></span>
                                <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><?= date('H:i', strtotime($event->event_time)) ?></span>
                            </div>
                        </div>
                        <span class="badge badge-<?= $event->status === 'po_open' ? 'warning' : ($event->status === 'public' ? 'success' : 'secondary') ?>">
                            <?= eventStatusLabel($event->status) ?>
                        </span>
                    </div>
                    
                    <?php if (empty($ticketClasses)): ?>
                        <div class="text-center text-muted" style="padding: 2rem;">
                            <p>Belum ada kelas tiket tersedia</p>
                        </div>
                    <?php else: ?>
                        <form action="<?= url('/checkout') ?>" method="POST" id="checkoutForm">
                            <?= csrfField() ?>
                            <input type="hidden" name="event_id" value="<?= $event->id ?>">
                            
                            <div class="mb-4" id="ticketClasses">
                                <?php
                                $firstAvailable = null;
                                foreach ($ticketClasses as $t) { if ($t->available() > 0) { $firstAvailable = $t->id; break; } }
                                foreach ($ticketClasses as $tc):
                                    $available = $tc->available();
                                    $isSoldOut = $available <= 0;
                                    $typeClass = 'ticket-type-' . preg_replace('/[^a-z0-9_-]/', '', strtolower($tc->class_type ?? 'cat'));
                                    $isSelected = !$isSoldOut && $tc->id === $firstAvailable;
                                    $isSeated = method_exists($tc, 'isSeated') && $tc->isSeated();
                                ?>
                                    <label class="ticket-class-option <?= $typeClass ?> <?= $isSelected ? 'selected' : '' ?> <?= $isSoldOut ? 'is-soldout' : '' ?>"
                                           data-price="<?= $tc->price ?>" data-max="<?= $available ?>" data-id="<?= $tc->id ?>" data-seated="<?= $isSeated ? '1' : '0' ?>">
                                        <input type="radio" name="ticket_class_id" value="<?= $tc->id ?>" class="ticket-radio-input"
                                               <?= $isSelected ? 'checked' : '' ?> <?= $isSoldOut ? 'disabled' : '' ?>>
                                        <span class="ticket-radio" aria-hidden="true"></span>

                                        <div class="d-flex justify-between items-start">
                                            <div>
                                                <div class="d-flex items-center gap-2 mb-1">
                                                    <span class="font-semibold"><?= h($tc->name) ?></span>
                                                    <span class="badge badge-primary"><?= $tc->getTypeLabel() ?></span>
                                                    <?php if ($isSoldOut): ?>
                                                        <span class="badge badge-danger">Habis</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($tc->section): ?>
                                                    <div class="text-sm text-muted">Seksi: <?= h($tc->section) ?></div>
                                                <?php endif; ?>
                                                <?php if ($tc->description): ?>
                                                    <div class="text-sm text-muted mt-1"><?= h($tc->description) ?></div>
                                                <?php endif; ?>
                                                <div class="text-sm text-muted mt-1">Tersedia: <strong><?= $available ?></strong> tiket</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-semibold text-lg" style="color: var(--color-primary);"><?= formatRupiah($tc->price) ?></div>
                                                <div class="text-sm text-muted">/ tiket</div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($tc->benefits): ?>
                                            <div class="mt-2 pt-2" style="border-top: 1px solid var(--color-border);">
                                                <div class="font-medium text-sm mb-1">Benefit:</div>
                                                <ul class="text-sm text-muted" style="margin-left: 1.5rem;">
                                                    <?php foreach (explode("\n", $tc->benefits) as $benefit): ?>
                                                        <?php if (trim($benefit)): ?>
                                                            <li><?= h(trim($benefit)) ?></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="form-group mb-4" id="qtyWrapper">
                                <label class="form-label">Jumlah Tiket</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline qty-minus" aria-label="Kurangi" style="width: 44px;">-</button>
                                    <input type="number" name="quantity" id="quantity" class="form-input qty-input text-center" value="1" min="1" max="<?= empty($ticketClasses) ? 10 : min(10, max(array_map(fn($tc)=>(int)$tc->capacity, $ticketClasses))) ?>" required>
                                    <button type="button" class="btn btn-outline qty-plus" aria-label="Tambah" style="width: 44px;">+</button>
                                </div>
                                <p class="form-help">Maksimal pembelian per transaksi: 10 tiket · stok menyesuaikan kelas terpilih</p>
                            </div>

                            <div class="form-group mb-4" id="seatWrapper" style="display:none;">
                                <label class="form-label">Pilih Kursi <span class="text-sm text-muted">(maks 10)</span></label>
                                <div class="seat-legend">
                                    <span><i class="seat-dot avail"></i> Tersedia</span>
                                    <span><i class="seat-dot sel"></i> Pilihanmu</span>
                                    <span><i class="seat-dot sold"></i> Terjual</span>
                                </div>
                                <div id="seatMap" class="seat-map" role="group" aria-label="Peta kursi"></div>
                                <p class="form-help" id="seatPicked">Belum ada kursi dipilih.</p>
                                <div id="seatInputs"></div>
                            </div>
                            
                            <?php $isPOEvent = $event->status === 'po_open'; ?>
                            <div class="form-group mb-4" id="poCodeWrapper" style="<?= $isPOEvent ? 'display:block;' : 'display:none;' ?>">
                                <?php if ($isPOEvent): ?>
                                    <div class="alert alert-warning mb-2" style="font-size:0.8125rem; padding:0.75rem 1rem;">Status Flash Sale — masukkan kode Flash Sale untuk membeli. Tanpa kode Flash Sale, checkout akan ditolak.</div>
                                <?php endif; ?>
                                <label class="form-label" for="po_code">Kode Flash Sale</label>
                                <div class="input-group">
                                    <input type="text" id="po_code" name="po_code" class="form-input" placeholder="Masukkan kode Flash Sale">
                                    <button type="button" class="btn btn-primary" id="checkPOCode">Cek</button>
                                </div>
                                <p class="form-help" id="poCodeMessage"></p>
                            </div>
                            
                            <div class="alert alert-info mb-4" style="font-size: 0.875rem;">
                                <strong>Ringkasan:</strong>
                                <div id="orderSummary" class="mt-2">
                                    <div class="d-flex justify-between">
                                        <span>Harga tiket</span>
                                        <span id="summaryPrice"><?= formatRupiah($ticketClasses[0]->price ?? 0) ?></span>
                                    </div>
                                    <div class="d-flex justify-between">
                                        <span>Jumlah</span>
                                        <span id="summaryQty">1</span>
                                    </div>
                                    <hr style="margin: 0.5rem 0; border-color: var(--color-border);">
                                    <div class="d-flex justify-between font-semibold">
                                        <span>Total</span>
                                        <span id="summaryTotal"><?= formatRupiah($ticketClasses[0]->price ?? 0) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2" id="checkoutActions">
                                <a href="<?= url('/events') ?>" class="btn btn-outline flex-1">Kembali</a>
                                <button type="submit" class="btn btn-primary btn-lg flex-1" id="checkoutBtn">
                                    Checkout
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ticketOptions = document.querySelectorAll('.ticket-class-option');
    const quantityInput = document.getElementById('quantity');
    const summaryPrice = document.getElementById('summaryPrice');
    const summaryQty = document.getElementById('summaryQty');
    const summaryTotal = document.getElementById('summaryTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const checkPOBtn = document.getElementById('checkPOCode');
    const poCodeInput = document.getElementById('po_code');
    const poCodeMessage = document.getElementById('poCodeMessage');
    const poWrapper = document.getElementById('poCodeWrapper');
    const qtyWrapper = document.getElementById('qtyWrapper');
    const seatWrapper = document.getElementById('seatWrapper');
    const seatMap = document.getElementById('seatMap');
    const seatPicked = document.getElementById('seatPicked');
    const seatInputs = document.getElementById('seatInputs');
    const checkoutForm = document.getElementById('checkoutForm');
    let selectedClass = null;
    let pickedSeats = [];
    let seatLayout = null;
    
    function updateSummary() {
        if (!selectedClass) return;
        const price = parseFloat(selectedClass.dataset.price);
        const seated = selectedClass.dataset.seated === '1';
        let qty;
        if (seated) {
            qty = pickedSeats.length || 1;
        } else {
            qty = parseInt(quantityInput.value) || 1;
            const max = parseInt(selectedClass.dataset.max) || 10;
            const capped = Math.min(max, 10);
            if (qty > capped) qty = capped;
            if (qty < 1) qty = 1;
            quantityInput.value = qty;
            quantityInput.max = capped;
        }
        const total = price * qty;

        summaryPrice.textContent = Artatix.formatRupiah(price);
        summaryQty.textContent = seated ? (pickedSeats.length ? pickedSeats.length + ' kursi' : '—') : qty;
        summaryTotal.textContent = Artatix.formatRupiah(total);
    }

    function isSeatedSelected() {
        return selectedClass && selectedClass.dataset.seated === '1';
    }

    function syncModeUI() {
        const seated = isSeatedSelected();
        if (qtyWrapper) qtyWrapper.style.display = seated ? 'none' : '';
        if (seatWrapper) seatWrapper.style.display = seated ? '' : 'none';
    }

    async function loadSeats() {
        pickedSeats = [];
        renderPicked();
        if (!seatMap || !selectedClass) return;
        const classId = selectedClass.dataset.id;
        seatMap.innerHTML = '<p class="text-sm text-muted">Memuat denah kursi…</p>';
        try {
            const res = await fetch('<?= url('/api/seats') ?>?class_id=' + encodeURIComponent(classId));
            const data = await res.json();
            seatLayout = data;
            renderSeatMap();
        } catch (e) {
            seatMap.innerHTML = '<p class="text-sm" style="color:var(--color-danger)">Gagal memuat denah kursi. Coba lagi.</p>';
        }
        updateSummary();
    }

    function renderSeatMap() {
        if (!seatMap || !seatLayout) return;
        const sold = new Set(seatLayout.sold || []);
        const layout = seatLayout.layout || { rows: [], cols: 0, blocked: [], prefix: '' };
        let html = '';
        (layout.rows || []).forEach(row => {
            html += '<div class="seat-row"><span class="seat-row-label">' + row + '</span>';
            for (let c = 1; c <= (layout.cols || 0); c++) {
                const label = (layout.prefix || '') + row + c;
                const isBlocked = (layout.blocked || []).includes(label);
                const isSold = sold.has(label);
                const cls = isSold ? 'sold' : (isBlocked ? 'blocked' : 'avail');
                const dis = (isSold || isBlocked) ? ' disabled' : '';
                html += '<button type="button" class="seat' + ' ' + cls + '"' + dis + ' data-seat="' + label + '" aria-label="Kursi ' + label + (isSold ? ' (terjual)' : '') + '">' + c + '</button>';
            }
            html += '</div>';
        });
        seatMap.innerHTML = html || '<p class="text-sm text-muted">Denah belum tersedia.</p>';
        seatMap.querySelectorAll('.seat.avail').forEach(btn => {
            btn.addEventListener('click', () => toggleSeat(btn.dataset.seat));
        });
    }

    function toggleSeat(label) {
        const i = pickedSeats.indexOf(label);
        if (i >= 0) {
            pickedSeats.splice(i, 1);
        } else {
            if (pickedSeats.length >= 10) {
                if (window.Artatix) Artatix.showToast('Maksimal 10 kursi per transaksi', 'warning');
                return;
            }
            pickedSeats.push(label);
        }
        renderPicked();
        updateSummary();
    }

    function renderPicked() {
        if (seatMap) {
            seatMap.querySelectorAll('.seat').forEach(btn => {
                btn.classList.toggle('sel', pickedSeats.includes(btn.dataset.seat));
            });
        }
        if (seatPicked) {
            seatPicked.textContent = pickedSeats.length
                ? 'Dipilih: ' + [...pickedSeats].sort().join(', ')
                : 'Belum ada kursi dipilih.';
        }
        if (seatInputs) {
            seatInputs.innerHTML = pickedSeats.map(s =>
                '<input type="hidden" name="seats[]" value="' + s.replace(/"/g, '') + '">'
            ).join('');
        }
    }
    
    function selectOption(option) {
        const radio = option.querySelector('.ticket-radio-input');
        if (!radio || radio.disabled) return;
        ticketOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        selectedClass = option;
        syncModeUI();
        if (isSeatedSelected()) {
            loadSeats();
        } else {
            updateSummary();
        }
    }

    ticketOptions.forEach(option => {
        const radio = option.querySelector('.ticket-radio-input');
        if (!radio || radio.disabled) return;
        radio.addEventListener('change', () => {
            if (radio.checked) selectOption(option);
        });
    });

    quantityInput.addEventListener('input', updateSummary);
    quantityInput.addEventListener('change', updateSummary);
    document.querySelectorAll('.qty-minus, .qty-plus').forEach(btn => {
        btn.addEventListener('click', () => setTimeout(updateSummary, 0));
    });

    // Initialize from server-rendered checked radio (first available), fallback to first enabled
    let initial = document.querySelector('.ticket-radio-input:checked:not([disabled])');
    let initialOption = initial ? initial.closest('.ticket-class-option') : null;
    if (!initialOption) initialOption = document.querySelector('.ticket-class-option:not(.is-soldout)');
    if (initialOption) selectOption(initialOption);

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (e) => {
            if (isSeatedSelected() && pickedSeats.length === 0) {
                e.preventDefault();
                if (window.Artatix) Artatix.showToast('Pilih minimal 1 kursi dulu', 'warning');
                else alert('Pilih minimal 1 kursi dulu');
            }
        });
    }
    
    // PO Code check
    if (checkPOBtn) {
        checkPOBtn.addEventListener('click', async () => {
            const code = poCodeInput.value.trim().toUpperCase();
            if (!code) {
                poCodeMessage.textContent = 'Masukkan kode Flash Sale terlebih dahulu';
                poCodeMessage.style.color = 'var(--color-danger)';
                return;
            }
            
            checkPOBtn.disabled = true;
            checkPOBtn.textContent = 'Memeriksa...';
            
            try {
                const res = await fetch('<?= url('/api/check-po-code') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ code, event_id: '<?= $event->id ?>' })
                });
                const data = await res.json();
                
                if (data.valid) {
                    poCodeMessage.textContent = 'Kode Flash Sale valid! Anda bisa membeli tiket Flash Sale.';
                    poCodeMessage.style.color = 'var(--color-success)';
                    poCodeInput.dataset.valid = 'true';
                } else {
                    poCodeMessage.textContent = data.message || 'Kode Flash Sale tidak valid atau sudah kedaluwarsa';
                    poCodeMessage.style.color = 'var(--color-danger)';
                    poCodeInput.dataset.valid = 'false';
                }
            } catch (e) {
                poCodeMessage.textContent = 'Terjadi kesalahan, coba lagi';
                poCodeMessage.style.color = 'var(--color-danger)';
            } finally {
                checkPOBtn.disabled = false;
                checkPOBtn.textContent = 'Cek';
            }
        });
    }
});
</script>