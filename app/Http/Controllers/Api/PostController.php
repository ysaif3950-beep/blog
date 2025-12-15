<?php

namespace App\Http\Controllers\Api;
use App\Models\Post; // <-- ده الموديل
use App\Models\Tag;
use App\Http\Requests\StorePostRequest; // الريكويست
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResourse;

class PostController extends Controller
{
    //
    public function show($id)
    {
        $post= Post::with(['user','tags'])->findOrFail($id);

        return new PostResource($post);

    }
    public function search(Request $request)
{
    $posts = Post::with(['user', 'tags'])
        ->where(function ($q) use ($request) {
            $q->where('title', 'like', "%{$request->search}%")
              ->orWhere('description', 'like', "%{$request->search}%");
        })
        ->paginate(15);

    return PostResource::collection($posts)->additional([
        'status' => 'success',
        'total'  => $posts->total(),
    ]);
}

     public function index()
    {
         $posts = Post::orderby('id','desc')->paginate(15);
        return PostResource::collection($posts);
    }
public function home()
    {
        $posts= Post::orderby('id','desc')->paginate(15);
        return PostResource::collection($posts);

    }
  public function create()
    {
        $tags=Tag::select('id','name')->get();
        return TagResource::collection($tags);

    }
public function edit($id)
    {
        $post=Post::findorfail($id);
        $tags=Tag::select('id','name')->get();
        $users=User::select('id','name')->get();
        return response()->json([

            'post'=> new PostResource($post),
            'users'=>UserResource::collection($users),
            'tags'=>TagResource::collection($tags),

        ],200);
    }
     public function update(UpdatePostRequest $request,$id)
    {
        $post=Post::findorfail($id);
        $old_image=$post->image;

        // التحقق من البيانات القادمة من الفورم
        $data = $request->validated();

        // لو فيه صورة جديدة مرفوعة
        if ($request->hasFile('image')) {
            // رفع الصورة الجديدة أولاً
            $path = $request->file('image')->store('uploads', 'public');

            // لو فيه صورة قديمة، امسحها بعد رفع الجديدة بنجاح
            if ($old_image && Storage::disk('public')->exists($old_image)) {
                Storage::disk('public')->delete($old_image);
            }

            // حفظ المسار الجديد داخل قاعدة البيانات
            $data['image'] = $path;
        }

        // تحديث البوست
        $post->update($data);
        $post->tags()->sync($request->input('tags', []));
           return response()->json([

                'post'=>new PostResource($post),
                'user'=>new UserResource($post->user),
                'tags' => TagResource::collection($post->tags),
           ], 200);
    }

    public function store(StorePostRequest $request)
    {
        // التحقق من البيانات القادمة من الفورم
        $data = $request->validated();

        // تحديد المستخدم الحالي تلقائياً
        $data['user_id'] = auth()->id();

        // لو فيه صورة مرفوعة
        if ($request->hasFile('image')) {
            // تخزين الصورة داخل فولدر images داخل storage/app/public
          $path = $request->file('image')->store('uploads', 'public');


            // حفظ المسار داخل قاعدة البيانات
            $data['image'] = $path;
        }

        // إنشاء البوست
        $post = Post::create($data);
        $post->tags()->sync($request->input('tags', []));

        // إعادة التوجيه بعد النجاح
        return response()->json([

                'post'=>new PostResource($post),
                'user'=>new UserResource($post->user),
                'tags' => TagResource::collection($post->tags),
           ], 200 );
    }

     public function destroy($id)
    {

        $post=Post::findorfail($id);
        $post->delete();
       return response()->json([
        'message' => 'Post deleted successfully '
    ], 200);
    }

}
