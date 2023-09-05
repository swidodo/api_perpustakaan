<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\AnggotaModel;

class AnggotaController extends Controller
{


    public function __construct()
    {
         $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout']]);
    }

    public function index(){
        $anggota['anggota'] = AnggotaModel::all();
        $data=[
            'code'   => 200,
            'status' => true,
            'data'   => $anggota
        ];
         return response()->json($data);
    }

    public function store(Request $request){
        if (! $request->isMethod('post')){
            $data = [
                'code'      => 405,
                'status'    => false,
                'message'   =>'method harus menggunakan post !'
            ];
            return response()->json($data);
            return true;
        }
         $validate = $this->validate($request, [
            'nama_lengkap'    => 'required|string',
            'jenis_kelamin'   => 'required|string',
            'no_hp'           => 'required|string',
            'email'           => 'required|email',
            'alamat'          => 'required|string',
        ]);
         $limit = AnggotaModel::select('id')->orderBy('id', 'DESC')->limit(1)->first();
         if ($limit == null){
            $no = 1;
         }else{
            $no = $limit->id + 1;
         }
         $code = "AGT.".date('m').".".date('Y').".".$no;
         
         $insert = AnggotaModel::create([
            'code_anggota'      => $code,
            'nama_lengkap'      => $request->nama_lengkap,
            'jenis_kelamin'     => $request->jenis_kelamin,
            'no_hp'             => $request->no_hp,
            'email'             => $request->email,
            'alamat'            => $request->alamat,
         ]);

         if ($insert){
            $data = [
                'code'      => 200,
                'status'    => true,
                'message'   => 'Data berhasil disimpan !'
            ];
         }else{
             $data=[
                'code'   => 500,
                'status' => false,
                'message' => 'Data gagal diupdate !'
            ];
         }

         return response()->json($data);

    }

    public function edit($id){
        $anggota['anggota'] = AnggotaModel::where('id',$id)->first();
        if ($anggota['anggota'] != NULL){
             $data=[
                'code'   => 200,
                'status' => true,
                'data'   => $anggota
            ];
        }else{
             $data=[
                'code'      => 404,
                'status'    => true,
                'message'   => 'Data tidak ditemukan !'
            ];
        }
        return response()->json($data);

    }

    public function update(Request $request,$id){
        $anggota = AnggotaModel::find($id);
        if (!$anggota){
             $data=[
                'code'      => 404,
                'status'    => true,
                'message'   => 'Data tidak ditemukan !'
            ];
            return response()->json($data);
            return true;
        }
         $validate = $this->validate($request, [
            'nama_lengkap'      => 'required|string',
            'jenis_kelamin'     => 'required|string',
            'no_hp'             => 'required|string',
            'email'             => 'required|email',
            'alamat'            => 'required|string',
        ]);
         $update = $request->all();
         $anggota->fill($update);
         if ($anggota->save()){
            $data=[
                'code'   => 200,
                'status' => true,
                'message' => 'Data telah diupdate !'
            ];
         }else{
            $data=[
                'code'   => 500,
                'status' => false,
                'message' => 'Data gagal diupdate !'
            ];
         }
         return response()->json($data);
    }

    public function destroy($id){
        $anggota = AnggotaModel::find($id);
        if (!$anggota){
            $data=[
                'code'   => 404,
                'status' => false,
                'message' => 'Data tidak tersedia !'
            ];
            return response()->json($data);
        }else{
            if($anggota->delete()){
                $data=[
                    'code'   => 200,
                    'status' => true,
                    'message' => 'Data telah dihapus !'
                ];
            }else{
                $data=[
                    'code'   => 500,
                    'status' => false,
                    'message' => 'Data gagal dihapus !'
                ];
            }
            return response()->json($data);
        }
    }
}