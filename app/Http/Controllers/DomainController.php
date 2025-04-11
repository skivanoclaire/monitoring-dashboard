<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DomainController extends Controller
{

    public function index()
    {
        
        $records = DB::table('dns_records')->where('type', 'A')->get();
        return view('domains.index', compact('records'));
        
    }
}
