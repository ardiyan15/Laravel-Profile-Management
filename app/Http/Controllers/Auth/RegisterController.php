<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // Gabung ketiga komponen tanggal
        $tanggal_lahir = $data["thn"].str_pad($data["bln"],2,0,STR_PAD_LEFT).str_pad($data["tgl"],2,0,STR_PAD_LEFT);

        $data['tanggal_lahir'] = $tanggal_lahir;

        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required','date','before:-10 years','after:-100 years'],
            'pekerjaan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kota' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio_profil' => ['sometimes', 'nullable', 'string'],
            'gambar_profil' => ['sometimes', 'file','image', 'max:2000'],
            'background_profil' => ['required', 'integer', 'min:1', 'max:12']
        ]);
    }

    protected function create(array $data)
    {
        // Gabung ketiga komponen tanggal
        $tanggal_lahir = $data["thn"].str_pad($data["bln"],2,0,STR_PAD_LEFT).str_pad($data["tgl"],2,0,STR_PAD_LEFT);

        // Ambil request object untuk proses upload file
        $request = request();

        $namaFile = 'default_profile.jpg';

        // proses upload file gambar profil
        if($request->hasfile('gambar_profil')) {
            // gunakan slug helper agar "nama" bisa dipakai sebagai bagian dari nama gambar profil
            $slug = Str::slug($data['nama']);

            // Ambil ekstensi file asli
            $extfile = $request->gambar_profil->getClientOriginalExtension();

            // Generate nama gambar, gabungan dari slug "nama"+time()+extensi file
            $namaFile = $slug.'-'.time().'.'.$extfile;

            // Proses upload, simpan ke dalam folder "uploads"
            $request->gambar_profil->storeAs('public/uploads',$namaFile);
        }

        // Input data ke database

        return User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'nama' => $data['nama'],
            'tanggal_lahir' => $tanggal_lahir,
            'pekerjaan' => $data['pekerjaan'],
            'kota' => $data['kota'],
            'bio_profil' => $data['bio_profil'],
            'gambar_profil' => $namaFile,
            'background_profil' => $data['background_profil']
        ]);
    }
}
