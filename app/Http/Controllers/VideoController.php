<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Video;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::all();
        return view('record', compact('videos'));
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $path = $file->store('videos', 'public');

            $video = new Video();
            $video->filename = $file->getClientOriginalName();
            $video->path = $path;
            $video->save();

            return response()->json(['message' => 'Video uploaded successfully'], 200);
        }

        return response()->json(['message' => 'No video uploaded'], 400);
    }
}
