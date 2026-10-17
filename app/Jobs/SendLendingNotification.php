<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendLendingNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int|string $lendingId,
        public int|string $memberId,
    ) {}

    public function handle(): void
    {
        // Simulasi notifikasi.
        // Nantinya dapat diganti dengan email atau Notification Service.
        Log::info('Lending notification processed', [
            'lending_id' => $this->lendingId,
            'member_id' => $this->memberId,
        ]);
    }
}
