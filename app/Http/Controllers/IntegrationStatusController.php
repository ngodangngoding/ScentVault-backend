<?php

namespace App\Http\Controllers;

use App\Http\Resources\IntegrationStatusResource;
use App\Models\Region;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Http;

#[Group('Admin - Integration Status', 'Endpoint khusus admin untuk memantau status integrasi layanan eksternal.', 11)]
class IntegrationStatusController extends Controller
{
    public function index()
    {
        $weatherStatus = $this->getWeatherStatus();
        $regionStatus = $this->getRegionStatus();

        return IntegrationStatusResource::collection(collect([
            [
                'key' => 'weather',
                'title' => 'API Cuaca',
                'status' => $weatherStatus['connected'] ? 'TERHUBUNG' : 'TIDAK TERHUBUNG',
                'connected' => $weatherStatus['connected'],
                'source' => 'BMKG',
                'detail' => $weatherStatus['detail'],
            ],
            [
                'key' => 'location',
                'title' => 'API Lokasi',
                'status' => $regionStatus['connected'] ? 'TERHUBUNG' : 'TIDAK TERHUBUNG',
                'connected' => $regionStatus['connected'],
                'source' => 'Master Region DB',
                'detail' => $regionStatus['detail'],
            ],
        ]));
    }

    protected function getWeatherStatus(): array
    {
        $baseUrl = config('services.bmkg.base_url');

        if (!$baseUrl) {
            return [
                'connected' => false,
                'detail' => 'BMKG_BASE_URL belum di-set.',
            ];
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout((int) config('services.bmkg.timeout', 10))
                ->withoutVerifying()
                ->acceptJson()
                ->get('/prakiraan-cuaca', [
                    'adm4' => '11.01.01.2004', 
                ]);

            return [
                'connected' => $response->successful(),
                'detail' => $response->successful()
                    ? 'Koneksi ke BMKG berhasil.'
                    : 'BMKG merespons status ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'detail' => $e->getMessage(),
            ];
        }
    }

    protected function getRegionStatus(): array
    {
        $exists = Region::query()->exists();

        return [
            'connected' => $exists,
            'detail' => $exists
                ? 'Master region tersedia di database.'
                : 'Data region belum tersedia. Jalankan sync-region.',
        ];
    }
}
