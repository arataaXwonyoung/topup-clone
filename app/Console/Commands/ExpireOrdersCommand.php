<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ExpireOrdersCommand extends Command
{
    protected $signature = 'orders:expire';
    protected $description = 'Mark expired orders';

    public function handle(OrderService $orderService): int
    {
        $count = $orderService->checkExpiredOrders();
        
        $this->info("Marked {$count} orders as expired.");
        
        return Command::SUCCESS;
    }
}