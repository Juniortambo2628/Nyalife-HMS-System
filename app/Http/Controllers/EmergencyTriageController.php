<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class EmergencyTriageController extends Controller
{
    public function create()
    {
        return Inertia::render('EmergencyTriage/Create');
    }
}
