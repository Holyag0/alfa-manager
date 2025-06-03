<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\StudentServiceInterface;
use App\Services\Contracts\ClassroomServiceInterface;
use App\Services\StudentService;
use App\Services\ClassroomService;
use App\Services\GuardianService;
use App\Services\EnrollmentService;

class ServiceLayerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind interfaces para implementações
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
        $this->app->bind(ClassroomServiceInterface::class, ClassroomService::class);

        // Registrar services como singletons
        $this->app->singleton(StudentService::class, function ($app) {
            return new StudentService();
        });

        $this->app->singleton(ClassroomService::class, function ($app) {
            return new ClassroomService();
        });

        $this->app->singleton(GuardianService::class, function ($app) {
            return new GuardianService();
        });

        $this->app->singleton(EnrollmentService::class, function ($app) {
            return new EnrollmentService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}