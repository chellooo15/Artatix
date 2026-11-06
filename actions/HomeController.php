<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Repositories\Event;

class HomeController extends Controller
{
    public function index(): void
    {
        $events = Event::query()
            ->whereIn('status', ['po_open', 'public'])
            ->orderBy('event_date', 'ASC')
            ->limit(6)
            ->get();
        
        $this->view('home', [
            'title' => 'Beranda',
            'events' => $events,
            'main_class' => 'main-home',
        ]);
    }
}