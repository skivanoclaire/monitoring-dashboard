<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vm = $request->query('vm');

        // Ambil list VM unik
        $vms = DB::table('vm_logs')
            ->select('vm_name')
            ->distinct()
            ->pluck('vm_name');

        // Ambil data log (default: 1 VM pertama)
        $vmName = $vm ?? $vms->first();

        $logs = DB::table('vm_logs')
            ->where('vm_name', $vmName)
            ->orderBy('timestamp', 'asc')
            ->get();

        return view('dashboard', compact('vms', 'logs', 'vmName'));
    }
}