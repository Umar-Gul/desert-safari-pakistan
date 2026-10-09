<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Staff accounts. Route group is guarded by the "manage-users" gate (super_admin only). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->input('q'));

        $users = User::query()
            ->with('roles')
            ->whereHas('roles')
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $v = $request->validated();

        $user = User::create([
            'name' => $v['name'],
            'email' => $v['email'],
            'password' => $v['password'], // hashed by the model cast
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles([$v['role']]);

        return redirect()->route('admin.users.index')->with('success', 'Staff account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user->load('roles')]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $v = $request->validated();

        if ($user->is($request->user()) && ! $user->hasRole($v['role'])) {
            return back()->withInput()->with('error', 'You cannot change your own role.');
        }

        $user->fill(['name' => $v['name'], 'email' => $v['email']]);

        if (filled($v['password'] ?? null)) {
            $user->password = $v['password'];
        }

        $user->save();
        $user->syncRoles([$v['role']]);

        return redirect()->route('admin.users.index')->with('success', 'Staff account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Staff account deleted.');
    }
}