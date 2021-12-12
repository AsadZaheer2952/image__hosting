<?php namespace Image\ValidationRequests;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image\Abstracts\FormRequest;

class UserRegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|unique:users',
            'age' => 'required',
            'image' => 'required',
            'password' => 'required|min:8',
        ];

    }

    public function prepareRequest(){
        $request = $this;
        return [
            'name' => $request['name'],
            'email' => $request['email'],
            'age' => $request['age'],
            'image' => $this->uploadImage($request['image']),
            'password' => $request['password'],
        ];
    }

    public function uploadImage($file){
        $image_64 = $file;
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',')+1);
        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10).'.'.$extension;
        Storage::disk('local')->put("public/profile/$imageName", base64_decode($image) , 'public');
        return public_path('storage/profile/'.$imageName);
    }
}
