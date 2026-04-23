<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use App\Models\User;

class AuditLogController extends Controller
{


    public function index(Request $request)
    {
        $pageTitle = 'Audit Trail';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => null],
            ['label' => 'Audit Trail', 'url' => null],
        ];

        $activities = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $users = User::all(); // for the filter dropdown

        return view('audit-logs.index', compact('activities', 'users', 'pageTitle', 'breadcrumbs'));
    }
}
