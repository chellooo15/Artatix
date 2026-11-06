<div class="container" style="max-width: 500px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/') ?>" style="color: inherit;">Beranda</a> / 
        <a href="<?= url('/user/orders') ?>" style="color: inherit;">Pesanan Saya</a> / 
        <span>Tiket</span>
    </nav>
    
    <div class="card mb-4 overflow-hidden" style="border: 2px solid var(--color-primary);">
        <div style="height: 8px; background: linear-gradient(90deg, var(--color-primary), #8b5cf6);"></div>
        
        <div class="card-body" style="padding: 2rem;">
            <div class="text-center mb-4">
                <span class="badge badge-primary mb-3"><?= $ticketClass ? $ticketClass->getTypeLabel() : 'Tiket' ?></span>
                <h2 style="margin-bottom: 0.5rem;"><?= h($event->name) ?></h2>
                <div class="text-muted"><?= formatDate($event->event_date) ?> · <?= date('H:i', strtotime($event->event_time)) ?></div>
            </div>
            
            <div style="background: var(--color-bg); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
                <div class="text-sm text-muted mb-1">KODE TIKET</div>
                <div class="font-mono font-bold" style="font-size: 1.5rem; letter-spacing: 0.1em; color: var(--color-primary);"><?= h($ticket->ticket_code) ?></div>
                
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--color-border);">
                    <img src="<?= $ticket->qrDataUrl(220) ?>" alt="QR <?= h($ticket->ticket_code) ?>" width="220" height="220" style="margin: 0 auto; display:block; image-rendering: pixelated; border-radius: var(--radius); background: white; padding: 6px; border: 1px solid var(--color-border);">
                    <p class="text-sm text-muted mt-2">Tunjukkan QR code ini di pintu masuk — tiap tiket unik</p>
                </div>
            </div>
            
            <div class="grid grid-2 gap-4 mb-4">
                <div>
                    <div class="text-sm text-muted">Nama Pemegang</div>
                    <div class="font-medium"><?= h($ticket->holder_name) ?></div>
                </div>
                <div>
                    <div class="text-sm text-muted">Email</div>
                    <div class="font-medium text-sm"><?= h($ticket->holder_email) ?></div>
                </div>
            </div>
            
            <?php if ($ticketClass && $ticketClass->section): ?>
                <div class="mb-4">
                    <div class="text-sm text-muted">Seksi / Blok</div>
                    <div class="font-medium"><?= h($ticketClass->section) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($ticket->seat_label)): ?>
                <div class="mb-4 text-center" style="background: var(--color-primary-light); border-radius: var(--radius); padding: 1rem;">
                    <div class="text-sm text-muted">NOMOR KURSI</div>
                    <div class="font-bold" style="font-size: 2rem; letter-spacing: 0.1em; color: var(--color-primary);"><?= h($ticket->seat_label) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($event->venue_id)): 
                $v = \App\Repositories\Venue::find((int)$event->venue_id);
                if ($v): ?>
                    <div class="mb-4">
                        <div class="text-sm text-muted">Venue</div>
                        <div class="font-medium"><?= h($v->name) ?></div>
                        <div class="text-sm text-muted"><?= h($v->address) ?>, <?= h($v->city) ?></div>
                    </div>
            <?php endif; endif; ?>
            
            <div style="border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
                <h4 class="mb-3">Syarat & Ketentuan</h4>
                <ul class="text-sm text-muted" style="margin-left: 1.5rem; line-height: 2;">
                    <li>Tiket hanya berlaku untuk 1 (satu) orang pada tanggal & waktu acara yang tertera</li>
                    <li>Tunjukkan tiket ini (QR code) di pintu masuk bersama KTP/SIM/Identitas resmi</li>
                    <li>Tiket tidak dapat dikembalikan atau ditukar kecuali acara dibatalkan</li>
                    <li>Dilarang memalsukan, menduplikasi, atau menjual kembali tiket ini</li>
                    <li>Penyelenggara berhak menolak masuk jika tiket tidak valid / sudah digunakan</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card text-center">
        <div class="card-body">
            <p class="text-muted mb-4">Butuh bantuan?</p>
            <div class="d-flex gap-2 justify-center flex-wrap">
                <a href="<?= url('/user/orders/' . $order->id) ?>" class="btn btn-outline">Kembali ke Pesanan</a>
                <button onclick="window.print()" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>
</div>