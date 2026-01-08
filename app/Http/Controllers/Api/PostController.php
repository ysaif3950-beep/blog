<?php

namespace App\Http\Controllers\Api;
use App\Models\Post;
use App\Models\Tag;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class PostController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of posts.
     */
    public function index()
    {
        $posts = Post::orderBy('id', 'desc')->paginate(15);
        return $this->paginated(
            PostResource::collection($posts),
            'Posts retrieved successfully'
        );
    }

    /**
     * Home page posts listing.
     */
    public function home()
    {
        $posts = Post::orderBy('id', 'desc')->paginate(15);
        return $this->paginated(
            PostResource::collection($posts),
            'Posts retrieved successfully'
        );
    }

    /**
     * Display the specified post.
     */
    public function show($id)
    {
        $post = Post::with(['user', 'tags'])->findOrFail($id);
        return $this->successWithResource(
            new PostResource($post),
            'Post retrieved successfully'
        );
    }

    /**
     * Search for posts.
     */
    public function search(Request $request)
    {
        $posts = Post::with(['user', 'tags'])
            ->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            })
            ->paginate(15);

        return $this->paginated(
            PostResource::collection($posts),
            'Search results retrieved successfully'
        );
    }

    /**
     * Get data for creating a new post.
     */
    public function create()
    {
        $tags = Tag::select('id', 'name')->get();
        return $this->success(
            TagResource::collection($tags),
            'Tags for post creation retrieved successfully'
        );
    }

    /**
     * Get data for editing a post.
     */
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $tags = Tag::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();

        return $this->success([
            'post' => new PostResource($post),
            'users' => UserResource::collection($users),
            'tags' => TagResource::collection($tags),
        ], 'Post edit data retrieved successfully');
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');
            $data['image'] = $path;
        }

        $post = Post::create($data);
        $post->tags()->sync($request->input('tags', []));

        // Load relationships
        $post->load(['user', 'tags']);

        return $this->created(
            new PostResource($post),
            'Post created successfully'
        );
    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $old_image = $post->image;
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');

            if ($old_image && Storage::disk('public')->exists($old_image)) {
                Storage::disk('public')->delete($old_image);
            }

            $data['image'] = $path;
        }

        $post->update($data);
        $post->tags()->sync($request->input('tags', []));

        // Load relationships
        $post->load(['user', 'tags']);

        return $this->updated(
            new PostResource($post),
            'Post updated successfully'
        );
    }

    /**
     * Remove the specified post.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return $this->deleted('Post deleted successfully');
    }
}

