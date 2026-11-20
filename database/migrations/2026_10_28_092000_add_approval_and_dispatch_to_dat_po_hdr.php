<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah Role DIREKSI dan MANAJER di sys_roles jika belum ada
        if (Schema::hasTable('sys_roles')) {
            $now = now();
            $rolesToAdd = [
                [
                    'role_cd'    => 'DIREKSI',
                    'role_nm'    => 'Direksi / Pimpinan Perusahaan',
                    'desc_txt'   => 'Persetujuan dokumen operasional strategis, Purchase Order bernilai tinggi, dan kebijakan perusahaan.',
                    'is_system'  => true,
                    'created_by' => 'SYSTEM_APPROVAL_INIT',
                    'created_dt' => $now,
                    'updated_by' => 'SYSTEM_APPROVAL_INIT',
                    'updated_dt' => $now,
                    'active_st'  => true,
                    'deleted_st' => false,
                    'version_no' => 1,
                ],
                [
                    'role_cd'    => 'MANAJER',
                    'role_nm'    => 'Manajer Pengadaan & Operasional',
                    'desc_txt'   => 'Verifikasi dan persetujuan Purchase Order (PO), pengawasan pengadaan bahan, dan manajemen rantai pasok.',
                    'is_system'  => true,
                    'created_by' => 'SYSTEM_APPROVAL_INIT',
                    'created_dt' => $now,
                    'updated_by' => 'SYSTEM_APPROVAL_INIT',
                    'updated_dt' => $now,
                    'active_st'  => true,
                    'deleted_st' => false,
                    'version_no' => 1,
                ],
            ];

            foreach ($rolesToAdd as $role) {
                $exists = DB::table('sys_roles')->where('role_cd', $role['role_cd'])->exists();
                if (!$exists) {
                    DB::table('sys_roles')->insert($role);
                }
            }
        }

        // 2. Tambah kolom approval & email dispatch ke dat_po_hdr
        if (Schema::hasTable('dat_po_hdr')) {
            Schema::table('dat_po_hdr', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_po_hdr', 'approver_id')) {
                    $table->foreignId('approver_id')
                        ->nullable()
                        ->after('gudang_id')
                        ->constrained('users', 'id')
                        ->nullOnDelete()
                        ->comment('User atasan yang dipilih untuk menyetujui PO');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'approval_token')) {
                    $table->string('approval_token', 64)
                        ->nullable()
                        ->unique()
                        ->after('status_cd')
                        ->comment('Token unik untuk signed URL email approval dan verifikasi QR Code dokumen');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'approved_by')) {
                    $table->string('approved_by', 100)
                        ->nullable()
                        ->after('approval_token')
                        ->comment('Nama atasan yang menyetujui PO');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'approved_at')) {
                    $table->timestamp('approved_at')
                        ->nullable()
                        ->after('approved_by')
                        ->comment('Waktu persetujuan PO oleh atasan');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'approval_notes')) {
                    $table->text('approval_notes')
                        ->nullable()
                        ->after('approved_at')
                        ->comment('Catatan/pesan persetujuan dari atasan');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'rejected_by')) {
                    $table->string('rejected_by', 100)
                        ->nullable()
                        ->after('approval_notes')
                        ->comment('Nama atasan yang menolak PO');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'rejected_at')) {
                    $table->timestamp('rejected_at')
                        ->nullable()
                        ->after('rejected_by')
                        ->comment('Waktu penolakan PO');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'reject_reason')) {
                    $table->text('reject_reason')
                        ->nullable()
                        ->after('rejected_at')
                        ->comment('Alasan penolakan PO');
                }

                // Pelacakan Pengiriman Dokumen ke Supplier via Email
                if (!Schema::hasColumn('dat_po_hdr', 'sent_to_supplier_at')) {
                    $table->timestamp('sent_to_supplier_at')
                        ->nullable()
                        ->after('catatan_txt')
                        ->comment('Waktu dokumen PO dikirimkan ke email supplier');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'sent_to_supplier_by')) {
                    $table->string('sent_to_supplier_by', 100)
                        ->nullable()
                        ->after('sent_to_supplier_at')
                        ->comment('Nama admin yang mengirimkan email ke supplier');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'supplier_email_sent')) {
                    $table->string('supplier_email_sent', 150)
                        ->nullable()
                        ->after('sent_to_supplier_by')
                        ->comment('Alamat email tujuan supplier saat PO dikirim');
                }

                if (!Schema::hasColumn('dat_po_hdr', 'supplier_email_notes')) {
                    $table->text('supplier_email_notes')
                        ->nullable()
                        ->after('supplier_email_sent')
                        ->comment('Catatan pengantar khusus yang disertakan pada email supplier');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_po_hdr')) {
            Schema::table('dat_po_hdr', function (Blueprint $table) {
                $columns = [
                    'approver_id',
                    'approval_token',
                    'approved_by',
                    'approved_at',
                    'approval_notes',
                    'rejected_by',
                    'rejected_at',
                    'reject_reason',
                    'sent_to_supplier_at',
                    'sent_to_supplier_by',
                    'supplier_email_sent',
                    'supplier_email_notes',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('dat_po_hdr', $column)) {
                        if ($column === 'approver_id') {
                            $table->dropForeign(['approver_id']);
                        }
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
