<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

#[Signature('redis:redis-consumer')]
#[Description('Command description')]
class RedisConsumer extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Redis::subscribe(['new-book-published'], function (string $message) {
            Log::info("Redis Consumer New book is Published");
        });
    }
}
