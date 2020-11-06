<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function edit(User $user)
    {
        // Extract komponen tanggal_lahir dari database, input ke array $user
        $time = strtotime($user->tanggal_lahir);

        $user['tgl'] = date('d', $time);
        $user['bln'] = date('m', $time);
        $user['thn'] = date('Y', $time);

        return view('user.edit',compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Satukan ketiga komponen
        $tanggal_lahir = $request["thn"].str_pad($request["bln"],2,0,STR_PAD_LEFT).str_pad($request["tgl"],2,0,STR_PAD_LEFT);

        // Input ke dalam array $request agar $tanggal_lahir bisa ikut divalidasi
        $request['tanggal_lahir'] = $tanggal_lahir;

        $validateData = request()->validate([
            'email' => ['required', 'string', 'email', 'max:255','unique:users,email'.$user->id],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:-10 years', 'after:-100 years'],
            'pekerjaan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kota' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio_profil' => ['sometimes', 'nullable', 'string'],
            'gambar_profil' => ['sometimes', 'file', 'image', 'max:2000'],
            'background_profil' => ['required', 'integer', 'min:1', 'max:12']
        ]);

        // Foto Profil user sekarang
        $namaFile = $user->gambar_profil;

        // Proses upload file gambar profil
        if ($request->hasFile('gambar_profil')) {
            // gunakan slug helper agar "nama" bisa dipakai sebagai bagian dari nama gambar_profil
            $slug = Str::slug($request['nama']);

            // Ambil extensi file asli
            $extFile = $request->gambar_profil->getClientOriginalExtension();

            // Generate nama gambar, gabungan dari slug "nama"+time()+extensi file
            $namaFile = $slug.'-'.time().'-'.$extFile;

            // Proses upload, simpan ke dalam folder "uploads"
            $request->gambar_profil->storeAs('public/uploads', $namaFile);
        }

        $validateData['gambar_profil'] = $namaFile;

        $user->update($validateData);

        return redirect('/#member-list')->with(['pesan' => 'update', 'nama' => $user->nama]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect('/member-list').with(['pesan' => 'delete', 'nama' => $user->nama]);
    }
}
