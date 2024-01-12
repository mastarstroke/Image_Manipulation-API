<?php

namespace App\Http\Controllers\v1;

use App\Models\Albums;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AlbumResource;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return AlbumResource::collection(Albums::where('user_id', $request->user()->id)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAlbumRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlbumRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $album = Albums::create($data);

        return new AlbumResource($album);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Albums  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Albums $album)
    {
        if ($request->user()->id != $album->user_id) {
            return abort(403, 'Unathorized');
        }
        return new AlbumResource($album);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAlbumRequest  $request
     * @param  \App\Models\Albums  $album
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAlbumRequest $request, Albums $album)
    {
        if ($request->user()->id != $album->user_id) {
            return abort(403, 'Unathorized');
        }
        $album->update($request->all());
        
        return new AlbumResource($album);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Albums  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Albums $album)
    {
        if ($request->user()->id != $album->user_id) {
            return abort(403, 'Unathorized');
        }
        $album->delete();

        return response('', 204);
    }
}