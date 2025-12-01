<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('timestamp', 'desc');

        if ($request->has('user_id') && $request->user_id != 'all') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action') && $request->action != 'all') {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(20);

        $users = User::orderBy('name')->get();
        
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.logs.index', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'filters' => $request->all()
        ]);
    }
}