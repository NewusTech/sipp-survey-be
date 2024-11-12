<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'logout']]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Mendapatkan kredensial yang dikirimkan
            $credentials = $request->only('email', 'password');

            // Mencoba login dengan kredensial
            $token = Auth::guard('api')->attempt($credentials);

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Mendapatkan data pengguna yang terautentikasi
            $user = Auth::guard('api')->user();
            $user['token'] = $token;
            $user['type'] = 'bearer';

            // Mendapatkan nama-nama role sebagai array dan mengubahnya menjadi string
            $roleNames = $user->getRoleNames();  // Mengambil koleksi nama role
            $roleNamesString = $roleNames->implode(', '); // Mengubah koleksi menjadi string yang dipisahkan oleh koma

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'roles' => $roleNamesString,  // Mengirimkan roles dalam bentuk string
                    'token' => $token,
                    'type' => 'bearer',
                ],
                'message' => 'Berhasil login',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }




    public function refresh()
    {
        try {
            return response()->json([
                'user' => Auth::user(),
                'authorisation' => [
                    'token' => Auth::refresh(),
                    'type' => 'bearer',
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
