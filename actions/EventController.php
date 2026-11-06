<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Repositories\Event;
use App\Repositories\TicketClass;
use App\Repositories\Venue;

class EventController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $venueId = (int)($_GET['venue'] ?? 0);
        
        $query = Event::query()
            ->whereIn('status', ['po_open', 'public', 'sold_out'])
            ->orderBy('event_date', 'ASC');
        
        if ($status) {
            $query->where('status', '=', $status);
        }
        
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        if ($venueId) {
            $query->where('venue_id', '=', $venueId);
        }
        
        $pagination = $query->paginate(12, $page);
        $venues = Venue::all();
        
        $this->view('events/index', [
            'title' => 'Semua Acara',
            'events' => $pagination['data'],
            'pagination' => $pagination,
            'venues' => $venues,
            'filters' => compact('status', 'search', 'venueId'),
        ]);
    }
    
    public function show(int $id): void
    {
        $event = Event::find($id);
        
        if (!$event) {
            $this->abort(404, 'Acara tidak ditemukan');
        }
        
        $ticketClasses = $event->getAvailableClasses();
        $venue = $event->venue();
        
        $this->view('events/show', [
            'title' => $event->name,
            'event' => $event,
            'ticketClasses' => $ticketClasses,
            'venue' => $venue,
        ]);
    }

    public function seats(): void
    {
        $classId = (int)($_GET['class_id'] ?? 0);
        $tc = $classId ? TicketClass::find($classId) : null;

        if (!$tc || !$tc->is_active || !$tc->isSeated()) {
            $this->json(['seats' => [], 'sold' => [], 'layout' => ['rows' => [], 'cols' => 0, 'blocked' => [], 'prefix' => '']], 404);
        }

        $this->json([
            'seats' => $tc->seats(),
            'sold' => $tc->soldSeats(),
            'layout' => $tc->seatLayout(),
            'price' => (float)$tc->price,
        ]);
    }
}