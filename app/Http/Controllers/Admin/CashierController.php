<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Cashier;
use App\Models\Outlet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    private function cashierListQuery(?string $search = null)
    {
        $query = Cashier::query()->with(['user', 'outlet.owner']);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('outlet', function ($outletQuery) use ($search) {
                    $outletQuery->where('outlet_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                            $ownerQuery->where('brand_name', 'like', "%{$search}%");
                        });
                });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        try {
            if ($request->ajax() || $request->has('draw')) {
                $baseQuery = $this->cashierListQuery();

                $recordsTotal = (clone $baseQuery)->count();

                if ($search = $request->input('search.value')) {
                    $baseQuery = $this->cashierListQuery($search);
                }

                $recordsFiltered = (clone $baseQuery)->count();
                $start = max((int) $request->input('start', 0), 0);
                $length = (int) $request->input('length', 10);
                $length = $length > 0 ? min($length, 100) : 10;

                $cashiers = $baseQuery
                    ->latest('id')
                    ->skip($start)
                    ->take($length)
                    ->get();

                $data = $cashiers->map(function (Cashier $cashier, int $index) use ($start) {
                    $user = $cashier->user;
                    $outlet = $cashier->outlet;
                    $userImage = $user?->image ? asset($user->image) : asset('assets/img/default-user.png');
                    $isActive = (bool) ($outlet?->owner?->status ?? false);

                    return [
                        'number' => $start + $index + 1,
                        'cashier' => '<div style="display:flex;align-items:center;gap:10px;">'
                            . '<img src="' . e($userImage) . '" alt="Foto" style="width:50px;height:50px;object-fit:cover;border-radius:50%;">'
                            . '<div><div>' . e($user?->name ?? '-') . '</div>'
                            . '<div style="font-size:0.9em;color:gray;">' . e($user?->email ?? '-') . '</div></div></div>',
                        'outlet' => e($outlet?->outlet_name ?? '-'),
                        'email' => e($user?->email ?? '-'),
                        'status' => $isActive
                            ? '<span class="badge bg-success">Aktif</span>'
                            : '<span class="badge bg-secondary">Nonaktif</span>',
                        'actions' => '<a href="' . e(route('admin.cashiers.edit', $cashier->id)) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Sunting</a> '
                            . '<form action="' . e(route('admin.cashiers.destroy', $cashier->id)) . '" method="POST" class="d-inline">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin ingin menghapus kasir ini?\')">'
                            . '<i class="fas fa-trash"></i> Hapus</button></form>',
                    ];
                });

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            }

            $cashiers = collect();

            return view('admin.cashiers.index', compact('cashiers'));
        } catch (\Exception $e) {
            if ($request->ajax() || $request->has('draw')) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function exportData(Request $request)
    {
        try {
            $cashiers = $this->cashierListQuery($request->input('search'))
                ->latest('id')
                ->get();

            $data = $cashiers->map(function (Cashier $cashier, int $index) {
                $user = $cashier->user;
                $outlet = $cashier->outlet;
                $isActive = (bool) ($outlet?->owner?->status ?? false);

                return [
                    'number' => $index + 1,
                    'cashier' => $user?->name ?? '-',
                    'outlet' => $outlet?->outlet_name ?? '-',
                    'email' => $user?->email ?? '-',
                    'status' => $isActive ? 'Aktif' : 'Nonaktif',
                ];
            });

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function create()
    {
        try {
            $outlets = Outlet::all();
            return view('admin.cashiers.create', compact('outlets'));
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'outlet_id'=> 'nullable|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'cashier'
                ]);

                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                    $user->save();
                }

                $user->cashier()->create([
                    'outlet_id' => $request->outlet_id,
                    'status'    => false
                ]);
            });

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Cashier berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Cashier $cashier)
    {
        try {
            $cashier->load('user');
            $outlets = Outlet::all();
            return view('admin.cashiers.edit', compact('cashier', 'outlets'));
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Cashier $cashier)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $cashier->user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'outlet_id'=> 'nullable|exists:outlets,id',
        ]);

        try {
            DB::transaction(function () use ($request, $cashier) {
                $cashier->update([
                    'outlet_id' => $request->outlet_id,
                ]);

                $user = $cashier->user;
                $user->name = $request->name;
                $user->email = $request->email;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                if ($request->hasFile('image')) {
                    if ($user->image) {
                        $imageName = basename($user->image);
                        Storage::delete('public/images/users/' . $imageName);
                    }
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                }
                $user->save();
            });

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Cashier berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Cashier $cashier)
    {
        try {
            DB::transaction(function () use ($cashier) {
                $user = $cashier->user;
                if ($user->image) {
                    $imageName = basename($user->image);
                    Storage::delete('public/images/users/' . $imageName);
                }
                $user->delete();
            });

            return redirect()->route('admin.cashiers.index')
                ->with('success', 'Cashier berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.cashiers.index')
                ->with('error', $e->getMessage());
        }
    }
}
