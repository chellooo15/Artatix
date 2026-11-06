<?php
// expects $event array or object, handles both
$isArray = is_array($event);
$eventId = $isArray ? ($event['id'] ?? null) : ($event->id ?? null);
$eventName = $isArray ? ($event['name'] ?? '') : ($event->name ?? '');
$eventDate = $isArray ? ($event['event_date'] ?? '') : ($event->event_date ?? '');
$eventTime = $isArray ? ($event['event_time'] ?? '') : ($event->event_time ?? '');
$eventStatus = $isArray ? ($event['status'] ?? '') : ($event->status ?? '');
$eventBanner = $isArray ? ($event['banner_image'] ?? null) : ($event->banner_image ?? null);
$venueId = $isArray ? ($event['venue_id'] ?? null) : ($event->venue_id ?? null);
$venue = null;
if (!empty($venueId)) {
    $venue = \App\Repositories\Venue::find((int)$venueId);
}
?>
<?php $bannerUrl = eventBannerUrl($eventBanner); ?>
<article class="card event-card">
    <?php if ($bannerUrl): ?>
        <a href="<?= url('/events/' . $eventId) ?>" class="event-media" aria-label="<?= h($eventName) ?>">
            <img src="<?= $bannerUrl ?>" alt="<?= h($eventName) ?>" loading="lazy" width="640" height="360" class="event-media-img" onerror="this.closest('.event-media').classList.add('event-media-broken'); this.remove();">
        </a>
    <?php else: ?>
        <a href="<?= url('/events/' . $eventId) ?>" class="event-media event-placeholder" aria-label="<?= h($eventName) ?>">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
        </a>
    <?php endif; ?>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap mb-2">
            <span class="badge badge-<?= $eventStatus === 'po_open' ? 'warning' : ($eventStatus === 'public' ? 'success' : 'secondary') ?>">
                <?= eventStatusLabel($eventStatus) ?>
            </span>
        </div>
        <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem; line-height: 1.4;">
            <a href="<?= url('/events/' . $eventId) ?>" style="color: inherit;"><?= h($eventName) ?></a>
        </h3>
        <p class="text-sm text-muted mb-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <?= $eventDate ? formatDate($eventDate) : '-' ?> &middot; <?= $eventTime ? date('H:i', strtotime($eventTime)) : '-' ?>
        </p>
        <?php if ($venue): ?>
            <p class="text-sm text-muted mb-3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <?= h($venue->name) ?>, <?= h($venue->city) ?>
            </p>
        <?php endif; ?>
        <a href="<?= url('/events/' . $eventId) ?>" class="btn btn-primary btn-block" aria-label="Lihat detail <?= h($eventName) ?>">Lihat Detail & Beli</a>
    </div>
</article>
