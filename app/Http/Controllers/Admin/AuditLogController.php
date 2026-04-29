<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($user = $request->get('user_id')) {
            $query->where('user_id', $user);
        }

        if ($from = $request->get('date_from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
