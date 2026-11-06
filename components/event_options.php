<?php
// Shared event <option> list. Handles both Event objects (Event::all) and arrays (paginate).
// Expects: $events (array), $selectedEventId (int, optional)
$sel = $selectedEventId ?? ($filters['eventId'] ?? 0);
foreach ($events as $e):
    $isObj = is_object($e);
    $eid = $isObj ? $e->id : ($e['id'] ?? null);
    $ename = $isObj ? $e->name : ($e['name'] ?? '');
    $edate = $isObj ? ($e->event_date ?? null) : ($e['event_date'] ?? null);
?>
    <option value="<?= $eid ?>" <?= (int)$sel === (int)$eid ? 'selected' : '' ?>>
        <?= h($ename) ?><?= $edate ? ' (' . formatDate($edate) . ')' : '' ?>
    </option>
<?php endforeach; ?>
