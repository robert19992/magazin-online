<?php

namespace App\Providers;

use App\Models\Connection;
use App\Models\Order;
use App\Models\Product;
use App\Policies\ConnectionPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Connection::class => ConnectionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
} 