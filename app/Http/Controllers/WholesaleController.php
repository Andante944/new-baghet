<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WholesaleController extends Controller
{
    public function __invoke(): View
    {
        return view('wholesale');
    }
}
