<?php

namespace App\Http\Controllers\Api;
use App\Models\tag;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = tag::paginate(15);
        return $this->paginated(
            TagResource::collection($tags),
            'Tags retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
        ]);

        $tag = tag::create($data);
        
        return $this->created(
            new TagResource($tag),
            'Tag created successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(tag $tag)
    {
        return $this->successWithResource(
            new TagResource($tag),
            'Tag retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
        ]);

        $tag->update($data);

        return $this->updated(
            new TagResource($tag),
            'Tag updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(tag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();
        
        return $this->deleted('Tag deleted successfully');
    }
}

