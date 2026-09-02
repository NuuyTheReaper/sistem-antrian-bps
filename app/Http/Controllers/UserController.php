<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar petugas (hanya diakses Admin).
     */
    public function index()
    {
        $petugasList = User::whereIn('role', ['petugas', 'kepala_bps'])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.petugas.index', compact('petugasList'));
    }

    /**
     * Simpan akun petugas baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|string|in:petugas,kepala_bps',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('admin.petugas.index')->with('sukses', 'Akun Petugas berhasil ditambahkan.');
    }

    /**
     * Update data akun petugas.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|string|in:petugas,kepala_bps',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.petugas.index')->with('sukses', 'Akun Petugas berhasil diperbarui.');
    }

    /**
     * Hapus akun petugas.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Jangan izinkan admin menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.petugas.index')->with('info', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.petugas.index')->with('sukses', 'Akun Petugas berhasil dihapus.');
    }
}
