<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class ConfigUserProvider implements UserProvider
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function retrieveById($domain): ?Authenticatable
    {
        // Hanya mengizinkan satu user (Super Admin)
        if ($domain == 1) {
            return $this->getGenericUser();
        }
        return null;
    }

    public function retrieveByToken($domain, $token): ?Authenticatable
    {
        // Tidak berlaku, tidak ada token di sini
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Tidak berlaku
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // Cek apakah email yang dimasukkan user sama dengan email di config ENV
        if (isset($credentials['email']) && $credentials['email'] === $this->config['email']) {
            return $this->getGenericUser();
        }
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $configPassword = $user->getAuthPassword();
        return $credentials['password'] === $configPassword;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        return false;
    }

    // Metode Helper untuk membuat objek User generik tanpa mengambil dari DB
    protected function getGenericUser(): Authenticatable
    {
        // Menggunakan class GenericUser bawaan Laravel untuk representasi user
        return new \Illuminate\Auth\GenericUser([
            'id' => 1,
            'name' => $this->config['name'] ?? 'Super Admin',
            'email' => $this->config['email'],
            'password' => $this->config['password'],
            'role' => 'super_admin', // Tetapkan role kustom
        ]);
    }
}
