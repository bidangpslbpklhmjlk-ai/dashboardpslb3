<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public const STATUS = [
        LoginLog::SUCCESS         => ['Berhasil',          'bg-emerald-100 text-emerald-800'],
        LoginLog::WRONG_PASSWORD  => ['Password salah',    'bg-amber-100 text-amber-800'],
        LoginLog::DEVICE_MISMATCH => ['Perangkat berbeda', 'bg-red-100 text-red-800'],
        LoginLog::INACTIVE        => ['Akun nonaktif',     'bg-slate-200 text-slate-600'],
        LoginLog::NO_ACCESS       => ['Tanpa akses app',   'bg-slate-200 text-slate-600'],
    ];

    public function index(Request $request)
    {
        $query = LoginLog::with('user:id,name,email')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user')) {
            $query->where('user_id', $request->integer('user'));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('device_name', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        $logs       = $query->paginate(25)->withQueryString();
        $filterUser = $request->filled('user') ? User::find($request->integer('user')) : null;
        $statuses   = self::STATUS;

        return view('admin.riwayat-login.index', compact('logs', 'filterUser', 'statuses'));
    }
}