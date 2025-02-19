<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(Request $request){
        return Image::create($request->all());
    }
}
