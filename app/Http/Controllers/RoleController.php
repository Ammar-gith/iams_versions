<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            'permission:Delete roles|Update roles|Assign Permissions',
            ['only' => ['edit', 'update', 'destroy', 'assignPermission', 'addPermission']]
        );
    }

    public function index()
    {
        // Page title
        $pageTitle = 'User Roles &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'User Roles', 'url' => null], // The current page (no URL)
        ];

        $roles = Role::all();
        return view('user-management.roles.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'roles' => $roles
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Role &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Roles List', 'url' => route('userManagement.role.index')],
            ['label' => 'Add Role', 'url' => null], // The current page (no URL)
        ];

        return view('user-management.roles.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        $role = Role::create($request->all());

        return redirect()->route('userManagement.role.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Role &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Roles List', 'url' => route('userManagement.role.index')],
            ['label' => 'Update Role', 'url' => null], // The current page (no URL)
        ];

        $role = Role::find($id);

        return view('user-management.roles.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'role' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        $role = Role::find($id);
        $role->update($request->all());

        return redirect()->route('userManagement.role.index')
            ->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if ($role) {
            $role->delete();
            return response()->json(['success', 'Role deleted successfully'], 200);
        } else {
            return response()->json(['error', 'Role not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Roles &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Roles List', 'url' => route('userManagement.role.index')],
            ['label' => 'Role', 'url' => null], // The current page (no URL)
        ];

        $role = Role::find($id);

        return view('user-management.roles.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'role' => $role
            ]
        );
    }

    public function addPermission($id)
    {
        // Page title
        $pageTitle = 'Assign Permission &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Roles List', 'url' => route('userManagement.role.index')],
            ['label' => 'Assign Permission', 'url' => null], // The current page (no URL)
        ];

        $role = Role::find($id);
        $permissions = Permission::all();
        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')->all(); // first method to get all permissions of a role

        return view('user-management.roles.assign-permissions', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function assignPermission(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required',
        ]);

        $role = Role::find($id);
        $role->syncPermissions($request->permissions);

        return redirect()->route('userManagement.role.index')
            ->with('success', 'Permissions assigned successfully to' . ' ' . $role->name);
    }
}
