<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class EnsureActiveTenant
{
   public function handle(Request $request, Closure $next)
   {
      $user = auth()->user();
      $tenantId = session('active_tenant_id');

      // ❌ Belum pilih tenant
      if (! $tenantId) {
         return redirect()
            ->route('admin.index')
            ->with('error', 'Silakan pilih toko terlebih dahulu.');
      }

      // Ambil tenant
      $tenant = \App\Models\Tenant::where('id', $tenantId)
         ->where('isActive', true)
         ->first();

      if (! $tenant) {
         session()->forget('active_tenant_id');

         return redirect()
            ->route('admin.index')
            ->with('error', 'Tenant tidak ditemukan atau tidak aktif.');
      }

      // Superadmin bebas
      if ($user->role !== 'superadmin') {
         $hasAccess = $user->tenants()
            ->where('tenant_id', $tenantId)
            ->exists();

         if (! $hasAccess) {
            session()->forget('active_tenant_id');
            return back()->with('error', 'Anda tidak memiliki akses.');
         }
      }

      // Inject tenant ke request
      $request->merge([
         'tenant' => $tenant
      ]);

      return $next($request);
   }
}
