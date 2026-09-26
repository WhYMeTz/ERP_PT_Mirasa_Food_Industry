<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gudang\DatStokBatch;
use App\Models\Produksi\MstBomHdr;
use App\Services\Produksi\BomService;
use Tests\TestCase;

class PemakaianFifoResepTest extends TestCase
{
    public function test_alokasi_resep_fifo_service_prioritizes_oldest_batch(): void
    {
        $bom = MstBomHdr::where('bom_no', 'BOM-IFM-001')->first();
        $this->assertNotNull($bom, 'BOM-IFM-001 must exist');

        $bomService = app(BomService::class);
        $result = $bomService->alokasiBahanResepFifo(1, $bom->bom_id, 100);

        $this->assertEquals($bom->bom_id, $result['bom_id']);
        $this->assertEquals(100, $result['target_qty']);
        $this->assertNotEmpty($result['items']);

        // Singkong harus teralokasi ke batch terlama
        $singkongRow = collect($result['items'])->firstWhere('barang_cd', 'BRG-001-001');
        if ($singkongRow && $singkongRow['is_allocated']) {
            $this->assertNotEmpty($singkongRow['batch_no']);
            $this->assertGreaterThan(0, $singkongRow['qty_keluar']);
            $this->assertTrue($singkongRow['is_oldest']);
        }
    }

    public function test_alokasi_resep_fifo_ajax_endpoint_returns_json(): void
    {
        $user = User::first();
        $this->assertNotNull($user, 'User must exist');

        $bom = MstBomHdr::first();
        $this->assertNotNull($bom, 'At least one BOM must exist');

        $response = $this->actingAs($user)->getJson(route('gudang.pemakaian.alokasi-resep', [
            'gudang_id'  => 1,
            'bom_id'     => $bom->bom_id,
            'target_qty' => 50,
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'bom_id',
                'bom_no',
                'bom_nm',
                'target_qty',
                'batch_ukuran_qty',
                'items',
            ],
        ]);
    }
}
