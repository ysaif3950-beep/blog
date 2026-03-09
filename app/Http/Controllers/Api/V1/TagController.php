<?php

namespace App\Http\Controllers\Api\V1;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TagResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

use App\Http\Requests\Api\V1\StoreTagRequest;
use App\Http\Requests\Api\V1\UpdateTagRequest;

class TagController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->authorizeResource(Tag::class, 'tag');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::paginate(15);
        return $this->paginated(
            TagResource ::collection($tags),
            'Tags retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();

        $tag = Tag::create($data);
        
        return $this->created(
            new TagResource($tag),
            'Tag created successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        return $this->successWithResource(
            new TagResource($tag),
            'Tag retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $data = $request->validated();

        $tag->update($data);

        return $this->updated(
            new TagResource($tag),
            'Tag updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();
        
        return $this->deleted('Tag deleted successfully');
    }
}

