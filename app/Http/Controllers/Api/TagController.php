<?php

namespace App\Http\Controllers\Api;
use App\Models\tag;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        //

        $tags=tag::paginate(15);
        return TagResource::collection($tags)->additional(
            [
                'status'=>'success',
                'total'=>$tags->total(),
                'current_page'=>$tags->currentpage()
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
         $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
        ]);
       $tag= tag::create($data);
        return response()->json([
            'status'=>'success',
            'data'=>new TagResource($tag),
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(tag $tag)
    {
        //
         return response()->json([
            'status'=>'success',
            'data'=>new TagResource($tag),
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, tag $tag)
    {
        //
         $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
        ]);
        $tag->update($data);

         return response()->json([
            'status'=>'success',
            'data'=>new TagResource($tag),
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(tag $tag)
    {
        //
        $tag->posts()->detach();
        $tag->delete();
        return response()->json([
            'status'=>'success',
             'message' => 'Tag deleted successfully'
        ], 200, );
    }
}
