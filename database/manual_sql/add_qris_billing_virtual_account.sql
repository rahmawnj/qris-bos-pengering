-- Jalankan satu kali di database aplikasi sebelum deploy kode VA.
-- Backup database terlebih dahulu. Kolom payment_method default 'manual' menjaga transaksi lama tetap kompatibel.

ALTER TABLE qris_billing_payments
    ADD COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT 'manual' AFTER proof_of_payment,
    ADD COLUMN payment_provider VARCHAR(30) NULL AFTER payment_method,
    ADD COLUMN gateway_order_id VARCHAR(100) NULL AFTER payment_provider,
    ADD COLUMN va_bank VARCHAR(30) NULL AFTER gateway_order_id,
    ADD COLUMN va_number VARCHAR(100) NULL AFTER va_bank,
    ADD COLUMN expires_at DATETIME NULL AFTER va_number,
    ADD COLUMN gateway_transaction_id VARCHAR(100) NULL AFTER expires_at,
    ADD UNIQUE KEY qris_billing_payments_gateway_order_id_unique (gateway_order_id),
    ADD INDEX qris_billing_payments_provider_status_index (payment_provider, status);
