<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Delete permissions|Update permissions', ['only' => ['edit', 'update', 'destroy']]);
    }

    public function index()
    {
        // Page title
        $pageTitle = 'Permissions &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Permissions', 'url' => null], // The current page (no URL)
        ];

        $permissions = Permission::all();
        return view('user-management.permissions.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'permissions' => $permissions
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Permission &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Permissions List', 'url' => route('userManagement.permission.index')],
            ['label' => 'Add Permission', 'url' => null], // The current page (no URL)
        ];

        return view('user-management.permissions.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',
            'string',
            'max:255',
            'unique:permissions.name',
        ]);

        Permission::create([
            'name' => $request->name,
        ]);

        return redirect()->route('userManagement.permission.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Permission &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Permissions List', 'url' => route('userManagement.permission.index')],
            ['label' => 'Update Permission', 'url' => null], // The current page (no URL)
        ];

        $permission = Permission::find($id);
        return view('user-management.permissions.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'permission' => $permission
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'string',
            'max:255',
            'unique:permissions.name',
        ]);

        $permission = Permission::find($id);
        $permission->update($request->all());

        return redirect()->route('userManagement.permission.index')
            ->with('success', 'Permission updated successfully');
    }

    public function destroy($id)
    {
        $permission = Permission::find($id);

        if ($permission) {
            $permission->delete();
            return response()->json(['success' => 'Permission deleted successfully.']);
        } else {
            return response()->json(['error' => 'Permission not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Permission &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Permission', 'url' => null], // The current page (no URL)
        ];

        $permission = Permission::find($id);

        return view('user-management.permissions.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'permission' => $permission
            ]
        );
    }
}
