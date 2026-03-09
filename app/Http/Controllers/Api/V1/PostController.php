<?php

namespace App\Http\Controllers\Api\V1;
use App\Models\Post;
use App\Models\Tag;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Http\Resources\Api\V1\TagResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class PostController extends Controller
{
    use ApiResponse;
 public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }
    
    public function index()
    {
        $posts = Post::orderBy('id', 'desc')->paginate(15);
        return $this->paginated(
            PostResource::collection($posts),
            'Posts retrieved successfully'
        );
    }

    public function home()
    {
        $this->authorize('viewAny', Post::class);

        $posts = Post::orderBy('id', 'desc')->paginate(15);
        return $this->paginated(
            PostResource::collection($posts),
            'Posts retrieved successfully'
        );
    }

       public function show(Post $post)
    {
        $post = Post::with(['user', 'tags'])->findOrFail($post->id);
        return $this->successWithResource(
            new PostResource($post),
            'Post retrieved successfully'
        );
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Post::class);
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

    public function create()
    {
        $tags = Tag::select('id', 'name')->get();
        return $this->success(
            TagResource::collection($tags),
            'Tags for post creation retrieved successfully'
        );
    }

    public function edit(Post $post)
    {
        $tags = Tag::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();

        return $this->success([
            'post' => new PostResource($post),
            'users' => UserResource::collection($users),
            'tags' => TagResource::collection($tags),
        ], 'Post edit data retrieved successfully');
    }

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

        $post->load(['user', 'tags']);

        return $this->created(
            new PostResource($post),
            'Post created successfully'
        );
    }

    public function update(UpdatePostRequest $request,  Post $post)
    {
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

    public function destroy(Post $post)
    {
        $post->delete();

        return $this->deleted('Post deleted successfully');
    }
}

