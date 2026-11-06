-- Artatix: seat maps (XXI-style) for ticket classes
-- Apply: mysql -h 127.0.0.1 -P 3306 -u artatix_user -p artatix < 002_seat_maps.sql
-- seat_mode: 'quantity' (default, existing behavior) or 'seated' (pick specific seats)
-- seat_layout JSON: {"rows":["A","B"],"cols":20,"blocked":["A1","B5"],"prefix":""}
-- tickets.seat_label: e.g. "A12" or "S-001" for standing slots

ALTER TABLE ticket_classes
    ADD COLUMN seat_mode ENUM('quantity','seated') NOT NULL DEFAULT 'quantity' AFTER capacity,
    ADD COLUMN seat_layout JSON NULL AFTER seat_mode;

ALTER TABLE tickets
    ADD COLUMN seat_label VARCHAR(16) NULL AFTER ticket_code,
    ADD KEY idx_tickets_seat (order_id, seat_label);
