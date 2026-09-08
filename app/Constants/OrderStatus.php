<?php

namespace App\Constants;

class OrderStatus
{
    // Definisi public const telah dihapus untuk menghindari duplikasi key string.
    // Sekarang, array STATUSES adalah satu-satunya sumber kebenaran untuk key status.
    // Perlu diingat bahwa ini akan mengurangi autokompletasi IDE untuk key status
    // dan kamu perlu menggunakan string literal (misal: 'received') atau
    // mengambil key dari OrderStatus::all() saat merujuk status.

    /**
     * Ini adalah sumber kebenaran utama untuk definisi setiap status.
     * Ketika kamu menambah status baru, cukup tambahkan satu entri di sini.
     * Setiap entri adalah array asosiatif yang berisi detail status.
     */
    const STATUSES = [
        'received' => [ // Menggunakan string literal sebagai key
            'key' => 'received', // key status (snake_case)
            'label' => 'Pesanan Diterima', // label dalam bahasa Indonesia
            'is_final' => false, // properti tambahan, contoh
            // Kamu bisa menambahkan properti lain di sini: 'color', 'icon', 'description', dll.
        ],
        'in_progress' => [
            'key' => 'in_progress',
            'label' => 'Sedang Diproses',
            'is_final' => false,
        ],
        'ready_for_pickup' => [
            'key' => 'ready_for_pickup',
            'label' => 'Siap Diambil',
            'is_final' => false,
        ],
        'completed' => [
            'key' => 'completed',
            'label' => 'Selesai',
            'is_final' => true,
        ],
        
        // Ketika menambah status baru, cukup tambahkan di sini:
        // 'new_status_example' => [
        //     'key' => 'new_status_example',
        //     'label' => 'Status Baru Contoh',
        //     'is_final' => false,
        // ],
    ];

    /**
     * Mengembalikan semua key status yang tersedia.
     * @return array<string>
     */
    public static function all(): array
    {
        return array_keys(self::STATUSES);
    }

    /**
     * Mengembalikan label bahasa Indonesia untuk key status tertentu.
     * @param string $key Key status (misal: 'received').
     * @return string Label status (misal: 'Pesanan Diterima').
     */
    public static function label(string $key): string
    {
        // Langsung ambil dari array STATUSES, atau buat default jika tidak ada.
        return self::STATUSES[$key]['label'] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Mengembalikan array yang berisi key status yang dianggap sebagai status final.
     * Metode ini sekarang secara dinamis membaca properti 'is_final' dari setiap status.
     * @return array<string> Contoh: ['completed', 'cancelled']
     */
    public static function finalStatuses(): array
    {
        $final = [];
        foreach (self::STATUSES as $statusData) {
            if ($statusData['is_final']) {
                $final[] = $statusData['key'];
            }
        }
        return $final;
    }

    /**
     * Mengembalikan seluruh array detail untuk key status tertentu.
     * Ini sangat berguna jika kamu ingin mengakses 'key', 'label', 'is_final',
     * atau properti lain yang mungkin kamu tambahkan.
     * @param string $key Key status.
     * @return array|null Array detail status, atau null jika tidak ditemukan.
     */
    public static function get(string $key): ?array
    {
        return self::STATUSES[$key] ?? null;
    }

    /**
     * Mengembalikan semua array detail status.
     * @return array<array<string, mixed>> Array dari semua array detail status.
     * Contoh: [['key' => 'received', 'label' => 'Pesanan Diterima', 'is_final' => false], ...]
     */
    public static function allWithDetails(): array
    {
        return array_values(self::STATUSES);
    }
}