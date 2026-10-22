<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AllUsersController extends Controller
{
   public function profileEdit($id)
   {
      $title = 'Edit Profil';
      $user = User::findOrFail($id);
      $tenant = session('active_tenant_id');

      return view('pages.all-users.profile', compact('title', 'user'));
   }



   public function profilUpdate(Request $request, $id)
   {
      $authUser = auth()->user();
      $user = User::findOrFail($id);

      // ===============================
      // VALIDASI
      // ===============================
      $validated = $request->validate([
         'name' => 'nullable|string|max:100',
         'username' => [
            'required',
            'string',
            'max:50',
            Rule::unique('users', 'username')->ignore($user->id),
         ],
         'email' => [
            'required',
            'email',
            Rule::unique('users', 'email')->ignore($user->id),
         ],
         'current_password' => 'nullable|required_with:new_password',
         'new_password' => 'nullable|min:6',
      ]);

      try {
         DB::transaction(function () use ($validated, $user, $authUser) {

            /**
             * ❗ CEK PASSWORD SAAT INI
             * Jika diisi tapi salah → THROW EXCEPTION
             */
            if (
               !empty($validated['current_password']) &&
               !Hash::check($validated['current_password'], $user->password)
            ) {
               throw new \Exception('Password saat ini tidak sesuai.');
            }

            // ===============================
            // DATA UPDATE
            // ===============================
            $dataUpdate = [
               'username' => $validated['username'],
               'email'    => $validated['email'],
            ];

            // Superadmin saja yang boleh update name
            if ($authUser->role === 'superadmin') {
               $dataUpdate['name'] = $validated['name'];
            }

            // Update password jika lolos validasi
            if (!empty($validated['new_password'])) {
               $dataUpdate['password'] = Hash::make($validated['new_password']);
            }

            $user->update($dataUpdate);
         });

         // ✅ JIKA SEMUA AMAN
         return back()->with('success', 'Profil berhasil diperbarui.');
      } catch (\Exception $e) {

         // ❌ JIKA ADA ERROR → ROLLBACK
         return back()
            ->withErrors([
               'current_password' => $e->getMessage(),
            ])
            ->withInput();
      }
   }
}
