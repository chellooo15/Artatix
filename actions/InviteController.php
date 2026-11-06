<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\Event;
use App\Repositories\EventInvite;
use App\Repositories\EventOrganizer;
use App\Repositories\User;

class InviteController extends Controller
{
    public function show(string $token): void
    {
        $invite = EventInvite::findByToken($token);

        if (!$invite) {
            $this->abort(404, 'Link undangan tidak ditemukan');
        }

        $event = $invite->event();
        if (!$event) {
            $this->abort(404, 'Acara tidak ditemukan');
        }

        $usable = $invite->isUsable();
        $reason = $usable ? '' : $invite->unusableReason();

        $alreadyOrganizer = false;
        if (Auth::check() && !Auth::isAdmin()) {
            $alreadyOrganizer = EventOrganizer::isOrganizer((int)$event->id, (int)Auth::user()['id']);
        }

        $this->view('invite/show', [
            'title' => 'Undangan Organizer: ' . $event->name,
            'invite' => $invite,
            'event' => $event,
            'usable' => $usable,
            'reason' => $reason,
            'alreadyOrganizer' => $alreadyOrganizer,
        ]);
    }

    public function accept(string $token): void
    {
        Auth::requireAuth();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->redirect('/invite/' . $token);
        }

        $invite = EventInvite::findByToken($token);

        if (!$invite || !$invite->isUsable()) {
            Flash::error($invite ? $invite->unusableReason() : 'Link undangan tidak ditemukan');
            $this->redirect('/invite/' . $token);
        }

        $user = Auth::user();

        if ($user['role'] === 'admin') {
            Flash::error('Admin tidak perlu klaim undangan organizer');
            $this->redirect('/admin/events/' . $invite->event_id . '/edit');
        }

        $eventId = (int)$invite->event_id;
        $userId = (int)$user['id'];

        // Auto-upgrade user -> organizer on first claim
        if ($user['role'] === 'user') {
            $u = User::find($userId);
            if ($u) {
                $u->update(['role' => 'organizer']);
            }
        }

        // Idempotent: already organizer
        if (EventOrganizer::isOrganizer($eventId, $userId)) {
            Flash::success('Anda sudah menjadi organizer acara ini');
            $this->redirect(url('/admin/events'));
        }

        EventOrganizer::add($eventId, $userId, (bool)$invite->can_edit_info);

        $invite->used_count = (int)$invite->used_count + 1;
        $invite->update(['used_count' => $invite->used_count]);

        Flash::success('Berhasil! Anda kini organizer acara tersebut');
        $this->redirect(url('/admin/events'));
    }
}
