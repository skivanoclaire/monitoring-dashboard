<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MailLog;

class MailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = MailLog::query();

        if ($request->filled('start_date')) {
            $query->where('sent_at', '>=', $request->start_date . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('sent_at', '<=', $request->end_date . ' 23:59:59');
        }

        $logs = $query->orderByDesc('sent_at')->paginate(250);

        return view('mail_logs.index', compact('logs'));
    }
}
