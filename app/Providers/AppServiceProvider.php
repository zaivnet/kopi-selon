<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
   /**
    * Register any application services.
    */
   public function register(): void
   {
      //
   }

   /**
    * Bootstrap any application services.
    */
   public function boot(): void
   {
      Date::setLocale('id');
      Carbon::setLocale('id');
      setlocale(LC_TIME, 'id_ID.utf8');
      config(['app.locale' => 'id']); // optional
      config(['app.timezone' => 'Asia/Jakarta']);
   }
}
