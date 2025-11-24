public function toResponse($request): Response
{
    $user = Auth::user();
    $user->load('role'); 
    $roleName = $user->role?->name;

    if (!$roleName) {
        $defaultRole = \App\Models\Role::where('name', 'Karyawan Organik')->first();
        if ($defaultRole) {
            $user->role_id = $defaultRole->id;
            $user->save();
            $user->load('role');
            $roleName = $user->role->name;
        }
    }

    $redirectUrl = match ($roleName) {
        'Admin' => '/admin/dashboard',
        'Supervisor' => '/supervisor/dashboard',
        'Learning Partner' => '/lp/dashboard',
        'Karyawan Organik' => '/dashboard',
        default => '/dashboard',
    };

    return redirect()->intended($redirectUrl);
}