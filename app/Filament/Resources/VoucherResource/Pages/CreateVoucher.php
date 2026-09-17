<?php

namespace App\Filament\Resources\VoucherResource\Pages;

use App\Filament\Resources\VoucherResource;
use App\Models\Voucher;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;

    /**
     * Ensure total_nominal and no_bukti are accurately computed before record creation.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan no_bukti terisi dan unik (mencegah bentrok race condition)
        if (empty($data['no_bukti']) || Voucher::where('no_bukti', $data['no_bukti'])->exists()) {
            $data['no_bukti'] = VoucherResource::generateNoBukti(
                $data['jenis_voucher'] ?? 'BKM',
                $data['tanggal'] ?? now()->toDateString()
            );
        }

        $data['total_nominal'] = VoucherResource::calculateTotalNominal($data['transactions'] ?? []);

        return $data;
    }

    /**
     * After Filament creates the parent record AND saves relationship items to DB,
     * calculate total_nominal from DB and ensure all rows have correct kode_akun.
     */
    protected function afterCreate(): void
    {
        /** @var Voucher $record */
        $record = $this->getRecord();

        // Ensure all transaction rows carry the header's kode_akun
        $record->transactions()->update(['kode_akun' => $record->kode_akun]);

        // Recalculate total_nominal from actual saved DB transactions
        $total = (float) $record->transactions()->sum('nominal');
        $record->updateQuietly(['total_nominal' => $total]);
    }
}
