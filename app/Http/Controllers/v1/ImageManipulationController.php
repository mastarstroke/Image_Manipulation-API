<?php

namespace App\Http\Controllers\V1;

use App\Models\Albums;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use App\Models\ImageManipulation;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Resources\v1\ImageManipulationResource;
use App\Http\Requests\UpdateImageManipulationRequest;

use Illuminate\Http\Request;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ImageManipulationResource::collection(ImageManipulation::where('user_id', $request->user()->id)->paginate());
    }

    public function byAlbum(Request $request, Albums $album)
    {
        if ($request->user()->id != $image->user_id) {
            return abort(403, 'Unathorized');
        }
        $where = [
            'album_id' => $album->id,
        ];
        return ImageManipulationResource::collection(ImageManipulation::where($where)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ResizeImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();

        /** @var UploadedFile|string $image */
        $image = $all['image'];
        unset($all['image']);
        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => $request->user()->id
        ];

        if (isset($all['album_id'])){
            $album = Albums::find($all['album_id']);
            if ($request->user()->id != $album->user_id) {
                return abort(403, 'Unathorized');
            }

            $data['album_id'] = $all['album_id'];
        }

        $dir = 'images/' .Str::random(). '/';
        $absolutePath = public_path($dir);
        File::makeDirectory($absolutePath);

        // images/dash2j3da/test.jpg
            // images/dash2j3da/test-resized.jpg
            if ($image instanceof UploadedFile) {
                $data['name'] = $image->getClientOriginalName();
                //test.jpg -> test-resized.jpg
                $filename = pathInfo($data['name'], PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $originalPath = $absolutePath.$data['name'];

                $image->move($absolutePath, $data['name']);

            }
            else{
                $data['name'] = pathinfo($image, PATHINFO_BASENAME);
                $filename = pathinfo($image, PATHINFO_FILENAME);
                $extension = pathinfo($image, PATHINFO_EXTENSION);
                $originalPath = $absolutePath.$data['name'];

                copy($image, $originalPath);
            }
            $data['path'] = $dir.$data['name'];

            $w = $all['w'];
            $h = $all['h'] ?? false;

            list($width, $height, $image)= $this->getImageWidthAndHeight($w, $h, $originalPath);

            $resizedFilename = $filename. '-resized.'.$extension;

            $image->resize($width, $height)->save($absolutePath.$resizedFilename);
            $data['output_path'] = $dir.$resizedFilename;

            $imageManipulation = ImageManipulation::create($data);

            return new ImageManipulationResource ($imageManipulation);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, ImageManipulation $image)
    {
        if ($request->user()->id != $image->user_id) {
            return abort(403, 'Unathorized');
        }
        return new ImageManipulationResource($image);
    }
     
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ImageManipulation $image)
    {
        if ($request->user()->id != $image->user_id) {
            return abort(403, 'Unathorized');
        }
        $image->delete();
        return response('', 204);
    }

    protected function getImageWidthAndHeight($w, $h, string $originalPath)
    {
        // 1000 - 50% => 50px
        $image = Image::make($originalPath);
        $originalWidth = $image->width();
        $originalHeight = $image->Height();

        if (str_ends_with($w, '%')) {
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;

            $newWidth = $originalWidth * $ratioW / 100;
            $newHeight = $originalHeight * $ratioH / 100;

        } else {
            $newWidth = (float)$w;
            /**
             * $originalWidth - $newWidth
             * $originalHeight - $newHeight
             * ----------------------------
             * $newHeight = $originalHeight * $newWidth/$originalWidth
            */
            $newHeight = $h ? (float)$h : $originalHeight * $newWidth/$originalWidth; 
        }

        return [$newWidth, $newHeight, $image];
    }
}