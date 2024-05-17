<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validatedData = $request->validate([
                'nama' => 'required|string|max:255',
                'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // max size 2MB
            ]);
            
            $user->nama = $validatedData['nama'];

            if ($request->hasFile('photo')) {
                $fileNameWithExt = $request->file('photo')->getClientOriginalName();
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('photo')->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;

                $fileNameToStore =  str_replace(" ","", $fileNameToStore);
                $path = $request->file('photo')->storeAs('public/profile_photos', $fileNameToStore);
                $user->photo = '/profile_photos/'.$fileNameToStore;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Berhasil update data'
            ]); 

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
