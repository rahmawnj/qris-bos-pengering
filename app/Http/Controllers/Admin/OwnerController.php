<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OwnerController extends Controller
{
    private function ownerListQuery(?string $search = null)
    {
        $query = Owner::query()
            ->with(['user', 'outlets:id,owner_id,outlet_name,address,phone_number'])
            ->withCount('outlets');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('brand_email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        try {
            if ($request->ajax() || $request->has('draw')) {
                $baseQuery = $this->ownerListQuery();

                $recordsTotal = (clone $baseQuery)->count();

                if ($search = $request->input('search.value')) {
                    $baseQuery = $this->ownerListQuery($search);
                }

                $recordsFiltered = (clone $baseQuery)->count();
                $start = max((int) $request->input('start', 0), 0);
                $length = (int) $request->input('length', 10);
                $length = $length > 0 ? min($length, 100) : 10;

                $owners = $baseQuery
                    ->latest('id')
                    ->skip($start)
                    ->take($length)
                    ->get();

                $canImpersonate = auth()->guard('admin_config')->check();
                $csrf = csrf_token();

                $data = $owners->map(function (Owner $owner, int $index) use ($start, $canImpersonate, $csrf) {
                    $user = $owner->user;
                    $userImage = $user?->image ? asset($user->image) : asset('assets/img/default-user.png');
                    $brandLogo = $owner->brand_logo ? asset($owner->brand_logo) : asset('assets/img/logo.png');
                    $paymentType = ($owner->payment_account_type ?? 'general') === 'owner'
                        ? '<span class="badge bg-primary">Private</span>'
                        : '<span class="badge bg-info">General</span>';
                    $balance = 'Rp ' . number_format(max((int) ($owner->balance ?? 0), 0), 0, ',', '.');

                    $expiryDate = $owner->account_expires_at ? \Carbon\Carbon::parse($owner->account_expires_at) : null;
                    $expiryClass = 'text-secondary';
                    $expiryText = 'Tidak Terbatas';

                    if ($expiryDate) {
                        $isPast = $expiryDate->isPast();
                        $isExpiringSoon = !$isPast && now()->diffInDays($expiryDate, false) <= 30;
                        $expiryClass = $isPast || $isExpiringSoon ? 'text-danger fw-bold' : 'text-success';
                        $expiryText = e($expiryDate->format('d M Y'));

                        if ($isPast) {
                            $expiryText .= ' (Habis)';
                        } elseif ($isExpiringSoon) {
                            $expiryText .= ' (Hampir Habis)';
                        }
                    }

                    $outletRows = $owner->outlets->map(function ($outlet, int $outletIndex) {
                        return '<tr>'
                            . '<td>' . ($outletIndex + 1) . '</td>'
                            . '<td>' . e($outlet->outlet_name) . '</td>'
                            . '<td>' . e($outlet->address) . '</td>'
                            . '<td>' . e($outlet->phone_number) . '</td>'
                            . '</tr>';
                    })->implode('');

                    if ($outletRows === '') {
                        $outletRows = '<tr><td colspan="4" class="text-center text-muted">Belum ada outlet</td></tr>';
                    }

                    $outletTable = '<div class="table-responsive">'
                        . '<table class="table table-bordered mb-0">'
                        . '<thead><tr><th>No</th><th>Nama Outlet</th><th>Alamat</th><th>No. HP</th></tr></thead>'
                        . '<tbody>' . $outletRows . '</tbody>'
                        . '</table></div>';

                    $statusBadge = $owner->status
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-danger">Nonaktif</span>';

                    $updateForm = '<form action="' . e(route('admin.owners.update.status', $owner->id)) . '" method="POST">'
                        . '<input type="hidden" name="_token" value="' . e($csrf) . '">'
                        . '<input type="hidden" name="_method" value="PUT">'
                        . '<div class="mb-3"><label for="status_' . $owner->id . '" class="form-label">Status Akun</label>'
                        . '<select class="form-control" name="status" id="status_' . $owner->id . '" required>'
                        . '<option value="1"' . ($owner->status ? ' selected' : '') . '>Aktif</option>'
                        . '<option value="0"' . (!$owner->status ? ' selected' : '') . '>Nonaktif</option>'
                        . '</select></div>'
                        . '<div class="mb-3"><label for="account_expires_at_' . $owner->id . '" class="form-label">Masa Aktif Akun (Tanggal Berakhir)</label>'
                        . '<input type="date" class="form-control" name="account_expires_at" id="account_expires_at_' . $owner->id . '" value="' . e($expiryDate ? $expiryDate->format('Y-m-d') : '') . '">'
                        . '</div>'
                        . '<div class="modal-footer px-0 pb-0">'
                        . '<a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Batal</a>'
                        . '<button type="submit" class="btn btn-warning">Simpan Perubahan</button>'
                        . '</div></form>';

                    $actions = '<a href="' . e(route('admin.owners.edit', $owner->id)) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Sunting</a> ';



                    $actions .= '<form action="' . e(route('admin.owners.destroy', $owner)) . '" method="POST" class="d-inline">'
                        . '<input type="hidden" name="_token" value="' . e($csrf) . '">'
                        . '<input type="hidden" name="_method" value="DELETE">'
                        . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Anda yakin ingin menghapus pemilik ini?\')">'
                        . '<i class="fas fa-trash"></i> Hapus</button></form>';
   if ($canImpersonate) {
                        $actions .= '<form action="' . e(route('admin.owners.impersonate', $owner->id)) . '" method="POST" class="d-inline">'
                            . '<input type="hidden" name="_token" value="' . e($csrf) . '">'
                            . '<button type="submit" class="btn btn-info btn-sm" onclick="return confirm(\'Login sebagai pemilik ini?\')">'
                            . '<i class="fas fa-user-shield"></i> Login</button></form> ';
                    }
                    return [
                        'number' => $start + $index + 1,
                        'owner' => '<div style="display:flex;align-items:center;gap:10px;">'
                            . '<img src="' . e($userImage) . '" alt="Gambar Pemilik" style="width:50px;height:50px;object-fit:cover;border-radius:50%;">'
                            . '<div><div>' . e($user?->name ?? '-') . '</div><div style="font-size:0.9em;color:gray;">' . e($user?->email ?? '-') . '</div></div></div>',
                        'brand' => '<div style="display:flex;align-items:center;gap:10px;">'
                            . '<img src="' . e($brandLogo) . '" alt="Logo Brand" style="height:50px;object-fit:cover;border-radius:10%;">'
                            . '<div><div>' . e($owner->brand_name ?? '-') . '</div><div style="font-size:0.9em;color:gray;">' . e($owner->brand_email ?? '-') . '</div></div></div>',
                        'qris_type' => $paymentType,
                        'outlets' => '<button type="button" class="btn btn-link owner-outlets-modal" data-title="Daftar Outlet ' . e($user?->name ?? $owner->brand_name ?? '-') . '" data-content="' . e($outletTable) . '">' . $owner->outlets_count . ' Outlet</button>',
                        'balance' => '<strong class="text-success">' . e($balance) . '</strong>',
                        'expires' => '<span class="' . e($expiryClass) . '">' . $expiryText . '</span>'
                            . '<br><button type="button" class="btn btn-warning btn-sm mt-1 owner-update-modal" data-title="Update Status & Masa Aktif ' . e($owner->brand_name ?? '-') . '" data-content="' . e($updateForm) . '"><i class="fas fa-sync-alt"></i></button>',
                        'status' => $statusBadge,
                        'address' => e($owner->address ?? '-'),
                        'actions' => $actions,
                    ];
                });

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            }

            $owners = collect();

            return view('admin.owners.index', compact('owners'));

        } catch (\Exception $e) {
            if ($request->ajax() || $request->has('draw')) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            // Lebih baik log errornya, dan tampilkan pesan umum ke user
            Log::error("Error in Admin Owner Index: " . $e->getMessage());
            return redirect()->route('admin.dashboard') // Redirect ke dashboard atau halaman lain yang aman
                ->with('error', 'Terjadi kesalahan saat memuat data owner. Silakan coba lagi.');
        }
    }

    public function exportData(Request $request)
    {
        try {
            $owners = $this->ownerListQuery($request->input('search'))
                ->latest('id')
                ->get();

            $data = $owners->map(function (Owner $owner, int $index) {
                $expiryDate = $owner->account_expires_at ? \Carbon\Carbon::parse($owner->account_expires_at) : null;
                $expiryText = 'Tidak Terbatas';

                if ($expiryDate) {
                    $expiryText = $expiryDate->format('d M Y');

                    if ($expiryDate->isPast()) {
                        $expiryText .= ' (Habis)';
                    } elseif (now()->diffInDays($expiryDate, false) <= 30) {
                        $expiryText .= ' (Hampir Habis)';
                    }
                }

                return [
                    'number' => $index + 1,
                    'owner' => trim(($owner->user?->name ?? '-') . ' / ' . ($owner->user?->email ?? '-')),
                    'brand' => trim(($owner->brand_name ?? '-') . ' / ' . ($owner->brand_email ?? '-')),
                    'qris_type' => ($owner->payment_account_type ?? 'general') === 'owner' ? 'Private' : 'General',
                    'outlets' => $owner->outlets_count . ' Outlet',
                    'balance' => 'Rp ' . number_format(max((int) ($owner->balance ?? 0), 0), 0, ',', '.'),
                    'expires' => $expiryText,
                    'status' => $owner->status ? 'Aktif' : 'Nonaktif',
                    'address' => $owner->address ?? '-',
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
            return view('admin.owners.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'email'                   => 'required|email|unique:users,email',
            'password'                => 'required|string|min:8|confirmed',
            'address'                 => 'required|string',
            'brand_name'              => 'required|string|max:255',
            'brand_email'             => 'required|email|unique:owners,brand_email',
            'payment_account_type'    => ['required', Rule::in(['general', 'owner'])],
            'merchant_id'             => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => $request->input('payment_account_type') === 'owner')],
            'withdrawal_fee_charged'  => ['required', 'boolean'],
            'image'                   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'brand_logo'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Buat record user terlebih dahulu
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'owner'
                ]);

                // Jika ada file gambar, simpan dan perbarui field image pada user
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('public/images/users');
                    $user->image = Storage::url($path);
                    $user->save();
                }

                $brandLogoPath = null;
                if ($request->hasFile('brand_logo')) {
                    $brandLogoPath = $request->file('brand_logo')->store('brand_logos', 'public');
                }

                // Buat record owner terkait user
                $user->owner()->create([
                    'address' => $request->address,
                    'brand_name' => $request->brand_name,
                    'brand_email' => $request->brand_email,
                    'brand_logo' => $brandLogoPath,
                    'payment_account_type' => $request->input('payment_account_type', 'general'),
                    'merchant_id' => $request->input('merchant_id'),
                    'withdrawal_fee_charged' => $request->boolean('withdrawal_fee_charged'),
                ]);
            });

            return redirect()->route('admin.owners.index')
                ->with('success', 'Owner berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function show(Owner $owner)
    {
        try {
            return view('admin.owners.show', compact('owner'));
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Owner $owner)
    {
        try {
            return view('admin.owners.edit', compact('owner'));
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Owner $owner)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email,' . $owner->user->id,
            'password'             => 'nullable|string|min:8|confirmed',
            'address'              => 'required|string',
            'brand_name'           => 'required|string|max:255',
            'brand_email'          => 'required|email|unique:owners,brand_email,' . $owner->id,
            'payment_account_type' => ['required', Rule::in(['general', 'owner'])],
            'merchant_id'          => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => $request->input('payment_account_type') === 'owner')],
            'withdrawal_fee_charged' => ['required', 'boolean'],
            'image'                => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'brand_logo'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $owner) {
                // Handle Brand Logo
                if ($request->hasFile('brand_logo')) {
                    if ($owner->brand_logo && Storage::disk('public')->exists($owner->brand_logo)) {
                        Storage::disk('public')->delete($owner->brand_logo);
                    }
                    $owner->brand_logo = $request->file('brand_logo')->store('brand_logos', 'public');
                }

                // Update data owner
                $owner->update([
                    'address'              => $request->address,
                    'brand_name'           => $request->brand_name,
                    'brand_email'          => $request->brand_email,
                    'payment_account_type' => $request->input('payment_account_type', 'general'),
                    'merchant_id'          => $request->input('merchant_id'),
                    'withdrawal_fee_charged' => $request->boolean('withdrawal_fee_charged'),
                ]);

                // Update data user
                $user = $owner->user;
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

            return redirect()->route('admin.owners.index')
                ->with('success', 'Owner berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Owner $owner)
    {
        try {
            DB::transaction(function () use ($owner) {
                $user = $owner->user;
                // Hapus gambar user jika ada
                if ($user->image) {
                    $imageName = basename($user->image);
                    Storage::delete('public/images/users/' . $imageName);
                }
                // Hapus record user yang otomatis akan menghapus record owner jika relasi disetting cascade
                $user->delete();
            });

            return redirect()->route('admin.owners.index')
                ->with('success', 'Owner berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }

    public function profile_update(Request $request, Owner $owner)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'email'                   => 'required|email|unique:users,email,' . $owner->user->id,
            'password'                => 'nullable|string|min:8|confirmed',
            'address'                 => 'required|string',
            'brand_name'              => 'required|string|max:255',
            'brand_email'             => 'required|email|unique:owners,brand_email,' . $owner->id,
            'payment_account_type'    => ['required', Rule::in(['general', 'owner'])],
            'merchant_id'             => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => $request->input('payment_account_type') === 'owner')],
            'withdrawal_fee_charged'  => ['required', 'boolean'],
            'image'                   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $owner) {
                $owner->update([
                    'address' => $request->address,
                    'brand_name' => $request->brand_name,
                    'brand_email' => $request->brand_email,
                    'payment_account_type' => $request->input('payment_account_type', 'general'),
                    'merchant_id' => $request->input('merchant_id'),
                    'withdrawal_fee_charged' => $request->boolean('withdrawal_fee_charged'),
                ]);

                $user = $owner->user;
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

            return redirect()->route('admin.owners.index')
                ->with('success', 'Profile Owner berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.owners.index')
                ->with('error', $e->getMessage());
        }
    }
}
