<?php

namespace App\Providers;

use App\Models\CourtCase;
use App\Policies\CourtCasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        CourtCase::class => CourtCasePolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
