<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Superadmin-only user CRUD.
 *
 * The superadmin can create / edit / disable users globally, and assign them
 * to one or more festivals through the festival_user pivot.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('festivals');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('pages.superadmin.users.index', compact('users'));
    }

    public function create()
    {
        $user = new User(['role' => 'promoter']);
        $festivals = Festival::orderByDesc('year')->orderBy('name')->get();
        return view('pages.superadmin.users.create', compact('user', 'festivals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8'],
            'role'      => ['required', Rule::in(['admin', 'promoter', 'sub_promoter', 'buyer'])],
            'festivals' => ['array'],
            'festivals.*' => ['integer', 'exists:festivals,id'],
            'roles'     => ['array'],
            'roles.*'   => ['nullable', Rule::in(['admin', 'promoter', 'sub_promoter'])],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
            ]);

            if (!empty($data['festivals'])) {
                foreach ($data['festivals'] as $festivalId) {
                    $roleInFestival = $data['roles'][$festivalId] ?? 'promoter';
                    $user->festivals()->attach($festivalId, [
                        'role_in_festival' => $roleInFestival,
                        'assigned_at'      => now(),
                    ]);
                }
            }
        });

        return redirect()->route('superadmin.users.index')->with('success', __('alert.user_created'));
    }

    public function edit(User $user)
    {
        $festivals = Festival::orderByDesc('year')->orderBy('name')->get();
        $assignments = $user->festivals()->get()->keyBy('id');
        return view('pages.superadmin.users.edit', compact('user', 'festivals', 'assignments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['required', Rule::in(['superadmin', 'admin', 'promoter', 'sub_promoter', 'buyer'])],
            'festivals' => ['array'],
            'festivals.*' => ['integer', 'exists:festivals,id'],
            'roles'     => ['array'],
            'roles.*'   => ['nullable', Rule::in(['admin', 'promoter', 'sub_promoter'])],
        ]);

        DB::transaction(function () use ($data, $user) {
            $user->name  = $data['name'];
            $user->email = $data['email'];
            $user->role  = $data['role'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            $sync = [];
            foreach ($data['festivals'] ?? [] as $festivalId) {
                $sync[$festivalId] = [
                    'role_in_festival' => $data['roles'][$festivalId] ?? 'promoter',
                    'assigned_at'      => now(),
                ];
            }
            $user->festivals()->sync($sync);
        });

        return redirect()->route('superadmin.users.index')->with('success', __('alert.user_updated'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('alert.user_cannot_delete_self'));
        }
        $user->delete();
        return redirect()->route('superadmin.users.index')->with('success', __('alert.user_deleted'));
    }
}