<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Post;
use App\Models\Tag;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResourse;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
class PostController extends Controller
{
    public function __construct()
    {
       $this->authorizeResource(Post::class, 'post');
    }
    private function clearPostCache(): void
    {
        Cache::tags(['posts'])->flush();
    }

    public function show(Post $post)
    {
        $post->load(['user', 'tags']);

        return view('posts.show', ['post' => $post]);
    }



    public function search(Request $request)
    {
       $posts= Post::with('user')->where
         ('description','like','%'.$request->search.'%')->
         orwhere('title','like','%'.$request->search.'%')->latest()->paginate(15);
        return view('posts.search',['posts'=>$posts]);
    }


 public function index()
{
    $page= request('page', 1);
    $cachekey= 'posts.index.page.'. $page;
    $posts = Cache::tags(['posts'])->remember($cachekey,now()->addMinutes(10), function(){
         return Post::with(['user', 'tags'])
        ->orderBy('id', 'desc')
        ->paginate(15);
    });
    return view('posts.index', ['posts' => $posts]);
}


    public function home()
{
    $page = request('page', 1);
    $cacheKey = "posts.home.page.{$page}";

    $posts = Cache::tags(['posts'])->remember(
        $cacheKey,
        now()->addMinutes(10),
        function () {
            return Post::with('user')
                ->latest('id')
                ->paginate(15);
        }
    );

    return view('home', ['posts' => $posts]);
}

    public function create()
    {
        $tags=Tag::select('id','name')->get();
        return view('posts.add',compact('tags') );
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
        $this->clearPostCache();
        return redirect()->route('posts.index')->with('success', 'تم إنشاء البوست بنجاح ✅');
    }


    public function edit(Post $post)
{
     $post->load('tags');
    $tags = Tag::select('id','name')->get();
    return view('posts.edit', ['post' => $post, 'tags' => $tags]);
}



     public function update(UpdatePostRequest $request, Post $post)
    {

        $data = $request->validated();

        $old_image=$post->image;


        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');

            if ($old_image && Storage::disk('public')->exists($old_image)) {
                Storage::disk('public')->delete($old_image);
            }

            $data['image'] = $path;
        }

        $post->update($data);
        $post->tags()->sync($request->input('tags', []));
        $this->clearPostCache();
        return redirect('posts')->with('success', 'Post updated successfully');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        $this->clearPostCache();

        return redirect()->back()->with('success', 'Post deleted successfully!');
    }

}
