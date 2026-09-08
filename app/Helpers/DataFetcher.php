<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Owner;
use App\Models\Outlet;
use App\Models\Addon;
use App\Models\Device;
use App\Models\Transaction;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class DataFetcher
{
    protected $rootEntity;

    protected $currentUser;

    public function __construct()
    {
        if (session('impersonating') && Auth::guard('web')->check()) {
            $this->currentUser = Auth::guard('web')->user();
        } else {
            $this->currentUser = Auth::user(); // Ini adalah user dari guard default
        }
        $this->rootEntity = $this->resolveRootEntity();
    }

    /**
     * Metode statis untuk menginisialisasi DataFetcher untuk user yang sedang login.
     * @return static
     */
    public static function forCurrentUser(): static
    {
        return new static();
    }

    /**
     * Memecahkan entitas root yang bertanggung jawab atas data.
     * Ini bisa Owner (untuk owner), Outlet (untuk kasir atau guard outlet), atau null.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function resolveRootEntity()
    {
        if (!$this->currentUser) {
            if (Auth::guard('admin_config')->check()) {
                // Super admin melihat semua data
                return null;
            }
            return null; // Tidak ada user login di guard default
        }

        // Eager load relasi yang mungkin dibutuhkan untuk menghindari N+1 problem
        if ($this->currentUser->role === 'owner') {
            $this->currentUser->loadMissing('owner');
            return $this->currentUser->owner;
        } elseif ($this->currentUser->role === 'cashier') {
            $this->currentUser->loadMissing('cashier.outlet.owner');
            $cashier = $this->currentUser->cashier;
            return $cashier ? $cashier->outlet : null;
        }
        return null; // Untuk admin atau member, biarkan __get menangani kasus umum
    }

    /**
     * Magic method untuk menangani akses properti dinamis (misal: ->devices, ->transactions).
     *
     * @param string $name Nama relasi yang diminta (misal: 'devices', 'transactions').
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \InvalidArgumentException
     */
    public function __get(string $name): Builder
    {
        // KASUS KHUSUS UNTUK ROOT ENTITY ADALAH OUTLET DAN DIMINTA 'outlets'
        // Ini adalah ketika Outlet login sebagai dirinya sendiri (via guard 'outlet')
        // atau Cashier login dan rootEntity-nya adalah Outlet mereka.
        // Dalam kasus ini, mereka hanya bisa melihat outlet mereka sendiri.
        if ($this->rootEntity instanceof Outlet && $name === 'outlets') {
            return Outlet::query()->where('id', $this->rootEntity->id);
        }

        // --- Logika yang ada sebelumnya tetap sama ---

        // Jika root entity sudah ada dan memiliki relasi langsung, kita pakai itu.
        // Ini berlaku untuk Owner->outlets (HasMany)
        if ($this->rootEntity && method_exists($this->rootEntity, $name)) {
            $relation = $this->rootEntity->$name();

            if (
                $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany ||
                $relation instanceof \Illuminate\Database\Eloquent\Relations\HasManyThrough
            ) {
                return $relation->getQuery();
            }

            throw new InvalidArgumentException(
                "The relation '{$name}' on " . get_class($this->rootEntity) . " is not a HasMany or HasManyThrough type. " .
                    "DataFetcher expects a collection-based relation (e.g., devices, transactions, outlets)."
            );
        }

        // Admin melihat semua data (termasuk admin_config)
        if (
            Auth::guard('admin_config')->check() ||
            ($this->currentUser && $this->currentUser->role === 'admin')
        ) {
            $modelClass = $this->getModelClassFromRelationName($name);
            if (!$modelClass) {
                throw new InvalidArgumentException("Could not determine model class for relation '{$name}' for admin user.");
            }
            return $modelClass::query();
        }

        // Fallback jika tidak ada user, root entity tidak memiliki relasi, atau role tidak relevan
        $fallbackModelClass = $this->getModelClassFromRelationName($name);
        if (!$fallbackModelClass) {
            $fallbackModelClass = User::class;
        }
        Log::warning("No specific data access method found for '{$name}' for user " . ($this->currentUser ? $this->currentUser->id : 'guest') . ". Returning empty query.");
        return $fallbackModelClass::query()->whereRaw('1 = 0');
    }

    /**
     * Memetakan nama relasi (plural) ke nama kelas model (singular).
     * Menggunakan Str::plural/singular untuk konvensi, dan custom mapping untuk kasus khusus.
     *
     * @param string $relationName
     * @return string|null Kelas model lengkap (misal: App\Models\Device::class)
     */
    protected function getModelClassFromRelationName(string $relationName): ?string
    {
        // Mapping eksplisit untuk memastikan kebenaran
        $customMapping = [
            'devices' => Device::class, // Menggunakan kelas yang sudah diimpor
            'transactions' => Transaction::class,
            'outlets' => Outlet::class, // Jika Anda ingin fetch semua outlets (misal untuk admin)
            'addons' => Addon::class, // Jika Anda ingin fetch semua outlets (misal untuk admin)
        ];

        if (isset($customMapping[$relationName])) {
            return $customMapping[$relationName];
        }

        // Coba konvensi Laravel: hapus 's' dan ubah ke StudlyCase
        $singularName = Str::singular($relationName);
        $modelClass = "App\\Models\\" . Str::studly($singularName);

        if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
            return $modelClass;
        }

        return null;
    }

    // Metode helper lainnya (getBrand, getOutlet) tetap sama atau disesuaikan jika perlu
    public function getBrand(): ?Owner
    {
        if ($this->rootEntity instanceof Owner) {
            return $this->rootEntity;
        } elseif ($this->rootEntity instanceof Outlet && $this->rootEntity->owner) {
            return $this->rootEntity->owner;
        }
        return null;
    }

    public function getOutlet(): ?Outlet
    {
        if ($this->rootEntity instanceof Outlet) {
            return $this->rootEntity;
        }
        return null;
    }

    /**
     * Mengembalikan daftar outlet id sesuai role.
     * - admin_config / admin: semua outlet
     * - owner/cashier: outlet miliknya
     */
    public function getOutletIds(): array
    {
        if (
            !session('impersonating') &&
            Auth::guard('admin_config')->check() &&
            !Auth::guard('web')->check()
        ) {
            return Outlet::pluck('id')->toArray();
        }

        if ($this->currentUser && $this->currentUser->role === 'admin') {
            return Outlet::pluck('id')->toArray();
            }

            if ($this->rootEntity instanceof Owner) {
        //   dd($this->rootEntity->outlets()->get());
            return $this->rootEntity->outlets()->pluck('id')->toArray();
        }

        if ($this->rootEntity instanceof Outlet) {
            return [$this->rootEntity->id];
        }

        return [];
    }

    protected array $permissions = [
        // Device
        'partner.device_list'                 => ['owner', 'cashier'],
        'partner.device.store'                => ['owner'],
        'partner.device.update'               => ['owner'],
        'partner.device.destroy'              => ['owner'],
        'partner.device.update_status'        => ['owner'],
        'partner.device.service_types.update' => ['owner'],

        // Outlet
        'partner.outlets.list'                 => ['owner'],
        'partner.outlets.detail'               => ['owner'],
        'partner.outlets.services.update'     => ['owner'],
        'partner.outlets.update'              => ['owner'],
        'partner.outlets.patch_update'        => ['owner'],
        'partner.outlets.store'               => [],
        'partner.outlets.destroy'             => [],
        'partner.outlets.update-status'       => ['owner'],

        // Addons
        'partner.addons.index'                => ['owner', 'cashier'],
        'partner.addons.create'               => ['owner'],
        'partner.addons.store'                => ['owner'],
        'partner.addons.edit'                 => ['owner'],
        'partner.addons.update'               => ['owner'],
        'partner.addons.destroy'              => ['owner'],

        // Cashier Payment
        'partner.cashier.payment.create'      => ['owner', 'cashier'],
        // 'partner.cashier.payment.store'       => ['owner', 'cashier'],

        // Service Order
        'partner.service-order.list'          => ['owner', 'cashier'],
        // 'partner.service-order.detail'        => ['owner', 'cashier'],
        // 'partner.service-orders.activate-device' => ['owner', 'cashier'],

        // // Dashboard
        // 'partner.dashboard'                   => ['owner', 'cashier'],

        // // Member
        // 'partner.members.verified'            => ['owner', 'cashier'],
        // 'partner.members.unverified'          => ['owner', 'cashier'],
        'partner.members.verify'              => ['owner', 'cashier'],
        'partner.members.subscription.destroy' => ['owner', 'cashier'],

        'partner.bypass.logs'                 => ['owner', 'cashier'],

        // // Bypass Log

        // // Transaction
        'partner.transactions.index'          => ['owner', 'cashier'],
        'partner.manual.transactions'         => ['owner', 'cashier'],
        'partner.qris.transactions'           => ['owner', 'cashier'],
        'partner.member.transactions'         => ['owner', 'cashier'],


        // // Cashiers
        // 'partner.cashiers.index'              => ['owner', 'cashier'],
        // 'partner.cashiers.create'             => ['owner', 'cashier'],
        // 'partner.cashiers.store'              => ['owner', 'cashier'],
        // 'partner.cashiers.edit'               => ['owner', 'cashier'],
        // 'partner.cashiers.update'             => ['owner', 'cashier'],
        // 'partner.cashiers.destroy'            => ['owner', 'cashier'],

        // // Receipt Config
        // 'partner.receipt.config.edit'         => ['owner', 'cashier'],
        // 'partner.receipt.config.update'       => ['owner', 'cashier'],


        // // Topup
        // 'partner.topup'                       => ['owner', 'cashier'],
        // 'partner.topup.histories'             => ['owner', 'cashier'],
        // 'partner.topup.store'                 => ['owner', 'cashier'],

        'withdrawal.request'                       => ['owner'],
        'withdrawal.histories'                      => ['owner'],


        // // Member Payment
        // 'partner.member.payment.create'       => ['owner', 'cashier'],
        // 'partner.member.payment.store'        => ['owner', 'cashier'],

    ];


    public function can(string $ability): bool
    {
        Log::info("Checking ability: {$ability}");

        // Ensure you are using the correct User model.
        // If your authenticated user might not always be an instance of App\Models\User,
        // you might need to fetch it explicitly or ensure your authentication guard
        // returns the correct model.
        $user = Auth::user();

        // 1. Cek apakah ada user yang login
        if (!$user) {
            Log::warning("No authenticated user for ability check: {$ability}");
            return false;
        }

        try {
            $userRole = $user->role;
            Log::info("Current user ID: {$user->id}, Role: {$userRole}");
        } catch (\Exception $e) {
            // This catch block handles cases where the 'role' attribute might genuinely be missing
            // or if the $user object is not an Eloquent model with the 'role' attribute.
            Log::error("Failed to get role for user ID: {$user->id}. Error: " . $e->getMessage());
            return false;
        }


        // 3. Cek apakah kemampuan (ability) terdaftar dalam permissions
        if (!isset($this->permissions[$ability])) {
            Log::warning("Undefined ability '{$ability}' checked. Defaulting to false.");
            return false;
        }

        $allowedRoles = $this->permissions[$ability];
        Log::info("Allowed roles for '{$ability}': " . implode(', ', $allowedRoles));

        // 4. Periksa apakah role user ada dalam daftar role yang diizinkan
        if (in_array($userRole, $allowedRoles)) {
            return true;
        }

        return false;
    }
}
