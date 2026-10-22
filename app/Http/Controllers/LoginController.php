<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

   public function login(Request $request)
   {
      $messages = [
         'login.required' => 'Email atau Username wajib diisi.',
         'password.required' => 'Password wajib diisi.',
         'password.min' => 'Password minimal 6 karakter.',
         'password.max' => 'Password maksimal 15 karakter.',
      ];

      $request->validate([
         'login' => 'required|string|max:255',
         'password' => 'required|string|min:6|max:15',
      ], $messages);

      $loginInput = $request->input('login');
      $loginField = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

      // Ambil user berdasarkan email/username
      $user = User::where($loginField, $loginInput)->first();

      if (!$user) {
         return back()->with('error', 'User tidak ditemukan.');
      }

      // Cek apakah user aktif
      if (!$user->isActive) {
         return back()->with('error', 'Akun Anda nonaktif. Silakan hubungi admin.');
      }

      // Cek password
      if (!Hash::check($request->password, $user->password)) {
         return back()->with('error', 'Password salah.');
      }

      // Login berhasil
      Auth::login($user);

      /**
       * ===============================
       * SUPERADMIN
       * ===============================
       */
      if ($user->isSuperadmin()) {
         return redirect()
            ->route('superadmin.dashboard')
            ->with('success', 'Login berhasil sebagai ' . $user->name);
      }

      /**
       * ===============================
       * KASIR
       * ===============================
       */
      if ($user->isKasir()) {

         // Dapatkan tenant yg terkait
         $tenantIds = $user->tenants->pluck('id');

         $shift = Shift::where('tenant_id', $tenantIds)->get();
         $now = Carbon::now()->format('H:i:s');

         $shiftAktif = Shift::where('tenant_id', $tenantIds)
            ->where(function ($q) use ($now) {
               $q->where(function ($q2) use ($now) {
                  // Shift normal (tidak lintas hari)
                  $q2->whereColumn('start_time', '<=', 'end_time')
                     ->whereTime('start_time', '<=', $now)
                     ->whereTime('end_time', '>=', $now);
               })
                  ->orWhere(function ($q2) use ($now) {
                     // Shift malam (lintas hari)
                     $q2->whereColumn('start_time', '>', 'end_time')
                        ->where(function ($q3) use ($now) {
                           $q3->whereTime('start_time', '<=', $now)
                              ->orWhereTime('end_time', '>=', $now);
                        });
                  });
            })
            ->first();

         // dd($shift, $shiftAktif);

         if ($tenantIds->count() === 0) {
            Auth::logout();
            return back()->with('error', 'Akun kasir tidak memiliki tenant.');
         }

         // cek status tenant
         $activeTenants = Tenant::whereIn('id', $tenantIds)->where('isActive', true)->get();
         if ($activeTenants->count() === 0) {
            Auth::logout();
            return back()->with('error', 'Akun kasir tidak memiliki tenant aktif.');
         }

         // Kasir hanya boleh 1 tenant
         if ($tenantIds->count() > 1) {
            Auth::logout();
            return back()->with('error', 'Kasir hanya boleh memiliki satu tenant.');
         }

         if (!$shift || !$shiftAktif) {
            Auth::logout();
            return back()->with('error', 'Tidak ada shift yang sedang berjalan.');
         }

         // Set tenant aktif
         session(['active_tenant_id' => $tenantIds->first()]);

         return redirect()->route('kasir.dashboard')->with('success', 'Berhasil login sebagai ' . $user->name);
      }

      /**
       * ===============================
       * ADMIN
       * ===============================
       */
      if ($user->isAdmin()) {
         return redirect()
            ->route('admin.index')
            ->with('success', 'Login berhasil sebagai ' . $user->name);
      }

      // Role tidak dikenal
      Auth::logout();
      return back()->with('error', 'Role pengguna tidak dikenali.');
   }


   public function logout(Request $request)
   {
      Auth::logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      return redirect('/login');
   }
}
