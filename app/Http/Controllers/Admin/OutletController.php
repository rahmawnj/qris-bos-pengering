<?php

namespace App\Http\Controllers\Admin;

use App\Models\Owner;
use App\Models\Outlet;
use App\Models\QrisBillingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OutletController extends Controller
{
    private function outletListQuery(?string $search = null)
    {
        $query = Outlet::query()
            ->with(['owner', 'cashiers.user', 'devices', 'qrisBillingPayments'])
            ->withCount(['cashiers', 'devices'])
            ->withCount([
                'transactions as qris_transactions_count' => function ($query) {
                    $query->where('type', 'qris');
                },
            ]);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('outlet_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('brand_name', 'like', "%{$search}%")
                            ->orWhere('brand_email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        try {
            if ($request->ajax() || $request->has('draw')) {
                $baseQuery = $this->outletListQuery();

                $recordsTotal = (clone $baseQuery)->count();

                if ($search = $request->input('search.value')) {
                    $baseQuery = $this->outletListQuery($search);
                }

                $recordsFiltered = (clone $baseQuery)->count();
                $start = max((int) $request->input('start', 0), 0);
                $length = (int) $request->input('length', 10);
                $length = $length > 0 ? min($length, 100) : 10;

                $outlets = $baseQuery
                    ->latest('id')
                    ->skip($start)
                    ->take($length)
                    ->get();

                $data = $outlets->map(function (Outlet $outlet, int $index) use ($start) {
                    $owner = $outlet->owner;
                    $logo = $owner?->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-user.png');
                    $qris = ($outlet->qris_transactions_count ?? 0) <= 0
                        ? '<span class="badge bg-secondary mb-1"><i class="fa fa-ban me-1"></i> Tidak Pakai QRIS</span>'
                            . '<div class="small text-muted mb-1">Belum ada transaksi QRIS</div>'
                        : '<span class="badge bg-success mb-1"><i class="fa fa-qrcode me-1"></i> Pakai QRIS</span>'
                            . '<div class="small text-muted mb-1">' . e($outlet->qris_transactions_count) . ' transaksi QRIS</div>';

                    if (!$outlet->qris_billing_enabled) {
                        $billing = '<span class="badge bg-light text-dark border">Tidak Aktif</span>';
                    } else {
                        $dueDate = $outlet->qris_billing_due_date;
                        $isExpired = $outlet->has_overdue_billing;
                        $activeUntil = $isExpired ? '' : '<tr>'
                            . '<td class="py-1 px-0 text-muted">Aktif sampai</td>'
                            . '<td class="py-1 px-0 text-end fw-bold text-dark">' . e($dueDate ? $dueDate->format('d/m/Y') : '-') . '</td>'
                            . '</tr>';

                        $billing = '<div class="mb-2">'
                            . '<div class="fw-bold ' . ($isExpired ? 'text-danger' : 'text-success') . ' fs-14px">Rp ' . number_format($outlet->qris_billing_amount, 0, ',', '.') . '</div>'
                            . '<span class="badge bg-' . ($isExpired ? 'danger' : 'success') . '">' . ($isExpired ? 'Expired' : 'Aktif') . '</span>'
                            . '</div>'
                            . '<table class="table table-sm table-borderless mb-0 text-start small" style="min-width: 150px;"><tbody>'
                            . $activeUntil
                            . '<tr><td class="py-1 px-0 text-muted">Jumlah device</td>'
                            . '<td class="py-1 px-0 text-end fw-bold text-dark">' . e($outlet->devices_count ?? $outlet->devices->count()) . '</td></tr>'
                            . '</tbody></table>';
                    }

                    $cashierRows = $outlet->cashiers->map(function ($cashier, int $cashierIndex) {
                        return '<tr>'
                            . '<td class="ps-3">' . ($cashierIndex + 1) . '</td>'
                            . '<td class="fw-bold text-dark">' . e($cashier->user->name ?? 'Kasir') . '</td>'
                            . '<td>' . e($cashier->user->email ?? '-') . '</td>'
                            . '<td class="text-center">' . ($cashier->status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>') . '</td>'
                            . '</tr>';
                    })->implode('');
                    $cashierRows = $cashierRows ?: '<tr><td colspan="4" class="text-center text-muted py-4">Belum ada kasir terdaftar di outlet ini.</td></tr>';
                    $cashierTable = '<div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">'
                        . '<thead class="bg-light"><tr><th>#</th><th>Nama Lengkap</th><th>Email</th><th>Status</th></tr></thead>'
                        . '<tbody>' . $cashierRows . '</tbody></table></div>';

                    $deviceRows = $outlet->devices->map(function ($device, int $deviceIndex) {
                        return '<tr>'
                            . '<td class="ps-3">' . ($deviceIndex + 1) . '</td>'
                            . '<td><span class="badge bg-dark">' . e($device->code) . '</span></td>'
                            . '<td class="fw-bold text-dark">' . e($device->name ?? 'Generic Device') . '</td>'
                            . '<td class="text-center">' . ($device->bypass_activation ? '<span class="text-success" title="' . e($device->bypass_note) . '"><i class="fa fa-check-circle"></i> Yes</span>' : '<span class="text-muted"><i class="fa fa-times-circle"></i> No</span>') . '</td>'
                            . '<td class="text-center">' . ($device->device_status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>') . '</td>'
                            . '</tr>';
                    })->implode('');
                    $deviceRows = $deviceRows ?: '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada perangkat terdaftar di outlet ini.</td></tr>';
                    $deviceTable = '<div class="table-responsive"><table class="table table-hover table-striped mb-0 align-middle">'
                        . '<thead class="bg-light"><tr><th>#</th><th>Kode Device</th><th>Nama Device</th><th>Bypass</th><th>Status</th></tr></thead>'
                        . '<tbody>' . $deviceRows . '</tbody></table></div>';

                    return [
                        'number' => $start + $index + 1,
                        'outlet' => '<div class="fw-bold text-dark">' . e($outlet->outlet_name) . '</div>'
                            . '<div class="text-muted small">' . e($outlet->code) . '</div>',
                        'owner' => '<div class="d-flex align-items-center">'
                            . '<div class="symbol symbol-40px me-3">'
                            . '<img src="' . e($logo) . '" alt="Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #eee;">'
                            . '</div>'
                            . '<div class="ms-2">'
                            . '<div class="fw-bold">' . e($owner?->brand_name ?? 'No Brand') . '</div>'
                            . '<div class="small text-muted">' . e($owner?->brand_email ?? '-') . '</div>'
                            . '</div></div>',
                        'address' => e(Str::limit($outlet->address ?? 'Tidak ada alamat', 40)),
                        'status' => $outlet->status
                            ? '<span class="badge bg-success">Aktif</span>'
                            : '<span class="badge bg-danger">Tidak Aktif</span>',
                        'qris' => $qris,
                        'billing' => $billing,
                        'cashiers' => '<button type="button" class="btn btn-primary btn-xs outlet-detail-modal"'
                            . ' data-title="' . e('Daftar Kasir - ' . $outlet->outlet_name) . '"'
                            . ' data-content="' . e($cashierTable) . '">'
                            . '<span class="badge bg-white text-primary me-1">' . e($outlet->cashiers_count ?? $outlet->cashiers->count()) . '</span> Detail</button>',
                        'devices' => '<button type="button" class="btn btn-success btn-xs outlet-detail-modal"'
                            . ' data-title="' . e('Daftar Perangkat - ' . $outlet->outlet_name) . '"'
                            . ' data-content="' . e($deviceTable) . '">'
                            . '<span class="badge bg-white text-success me-1">' . e($outlet->devices_count ?? $outlet->devices->count()) . '</span> Detail</button>',
                        'actions' => '<div class="d-flex justify-content-center gap-1">'
                            . '<a href="' . e(route('admin.outlets.edit', $outlet)) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>'
                            . '<form action="' . e(route('admin.outlets.destroy', $outlet)) . '" method="POST" class="d-inline">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menghapus outlet ini?\')">'
                            . '<i class="fas fa-trash"></i></button></form></div>',
                    ];
                });

                return response()->json([
                    'draw' => (int) $request->input('draw'),
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            }

            $outlets = collect();

            return view('admin.outlets.index', compact('outlets'));
        } catch (\Exception $e) {
            if ($request->ajax() || $request->has('draw')) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            return redirect()->route('admin.outlets.index')->with('error', $e->getMessage());
        }
    }

    public function exportData(Request $request)
    {
        try {
            $outlets = $this->outletListQuery($request->input('search'))
                ->latest('id')
                ->get();

            $data = $outlets->map(function (Outlet $outlet, int $index) {
                $owner = $outlet->owner;
                $hasQris = ($outlet->qris_transactions_count ?? 0) > 0;
                $billing = 'Tidak Aktif';

                if ($outlet->qris_billing_enabled) {
                    $billing = ($outlet->has_overdue_billing ? 'Expired' : 'Aktif')
                        . ' - Rp ' . number_format($outlet->qris_billing_amount, 0, ',', '.')
                        . ' - sampai ' . ($outlet->qris_billing_due_date ? $outlet->qris_billing_due_date->format('d/m/Y') : '-');
                }

                return [
                    'number' => $index + 1,
                    'outlet' => trim(($outlet->outlet_name ?? '-') . ' / ' . ($outlet->code ?? '-')),
                    'owner' => trim(($owner?->brand_name ?? '-') . ' / ' . ($owner?->brand_email ?? '-')),
                    'address' => $outlet->address ?? '-',
                    'status' => $outlet->status ? 'Aktif' : 'Tidak Aktif',
                    'qris' => $hasQris ? 'Pakai QRIS (' . $outlet->qris_transactions_count . ' transaksi)' : 'Tidak Pakai QRIS',
                    'billing' => $billing,
                    'cashiers' => ($outlet->cashiers_count ?? $outlet->cashiers->count()) . ' Kasir',
                    'devices' => ($outlet->devices_count ?? $outlet->devices->count()) . ' Device',
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
            $owners = Owner::all();
            return view('admin.outlets.create', compact('owners'));
        } catch (\Exception $e) {
            return redirect()->route('admin.outlets.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'outlet_name' => 'required|string|max:255',
            'address'   => 'required|string|max:500',
            'timezone'  => 'required|in:WIB,WITA,WIT',
            'owner_id'  => 'required|exists:owners,id',
            'status'    => 'required|boolean',
            'qris_billing_enabled' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $code = $this->generateUniqueCode();

                $data = [
                    'owner_id' => $request->owner_id,
                    'code'     => $code,
                    'address'  => $request->address,
                    'outlet_name' => $request->outlet_name,
                    'timezone' => $request->timezone,
                    'status' => (int) $request->status,
                    'qris_billing_enabled' => (int) $request->qris_billing_enabled,
                    'qris_billing_due_date' => null,
                ];

                $outlet = Outlet::create($data);
                $formattedId = str_pad($outlet->id, 4, '0', STR_PAD_LEFT);
                $randomNumber = mt_rand(10, 99);
                $username = $formattedId . $randomNumber;
                $outlet->update(['username' => $username]);
            });

            return redirect()->route('admin.outlets.index')
                ->with('success', 'Outlet berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.outlets.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Outlet $outlet)
    {
        try {
            $owners = Owner::all();
            return view('admin.outlets.edit', compact('outlet', 'owners'));
        } catch (\Exception $e) {
            return redirect()->route('admin.outlets.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Outlet $outlet)
    {
        $request->validate([
            'outlet_name' => 'required|string|max:255',
            'address'   => 'required|string|max:500',
            'timezone'  => 'required|in:WIB,WITA,WIT',
            'owner_id'  => 'required|exists:owners,id',
            'status'    => 'required|boolean',
            'qris_billing_enabled' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $outlet) {
                $data = [
                    'address'  => $request->address,
                    'timezone' => $request->timezone,
                    'owner_id' => $request->owner_id,
                    'outlet_name' => $request->outlet_name, // tambahkan ini
                    'status' => (int) $request->status,
                    'qris_billing_enabled' => (int) $request->qris_billing_enabled,
                ];


                $outlet->update($data);
            });

            return redirect()->route('admin.outlets.index')
                ->with('success', 'Outlet berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.outlets.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Outlet $outlet)
    {
        try {
            DB::transaction(function () use ($outlet) {

                $outlet->delete();
            });

            return redirect()->route('admin.outlets.index')
                ->with('success', 'Outlet berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.outlets.index')
                ->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, Outlet $outlet)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $outlet->status = $request->status;
        $outlet->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    public function markQrisBillingPaid(Outlet $outlet)
    {
        if (!$outlet->qris_billing_enabled) {
            return redirect()->route('admin.outlets.index')
                ->with('error', 'Outlet ini tidak memakai perpanjangan QRIS.');
        }

        $outlet->loadMissing(['devices', 'qrisBillingPayments']);
        $pendingPayment = $outlet->latestQrisBillingPayment('pending');
        $activeFrom = now()->copy()->startOfDay();
        $activeUntil = now()->copy()->addMonthNoOverflow()->startOfDay();

        $data = [
            'period_start' => $activeFrom->format('Y-m-d'),
            'period_end' => $activeUntil->format('Y-m-d'),
            'amount' => $pendingPayment?->amount ?: $outlet->qris_billing_amount,
            'status' => 'paid',
            'paid_at' => now(),
        ];

        DB::transaction(function () use ($outlet, $pendingPayment, $data, $activeUntil) {
            if ($pendingPayment) {
                $pendingPayment->update($data);
            } else {
                QrisBillingPayment::create(array_merge(['outlet_id' => $outlet->id], $data));
            }

            $outlet->update(['qris_billing_due_date' => $activeUntil->toDateString()]);
        });

        return redirect()->route('admin.outlets.index')
            ->with('success', 'Perpanjangan QRIS outlet ' . $outlet->outlet_name . ' berhasil dikonfirmasi.');
    }

    /**
     * Generate kode unik untuk outlet dengan format "OUT-XXXXXX"
     */
    private function generateUniqueCode()
    {
        do {
            $code = 'OUT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Outlet::where('code', $code)->exists());

        return $code;
    }

}
