<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Post; // <-- ده الموديل
use App\Models\Tag;
use App\Http\Requests\StorePostRequest; // الريكويست
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
     public function show($id)
    {
        $post= Post::findorfail($id);

        return view('posts.show',['post'=>$post]);
    }
    public function apishow($id)
    {
        $post= Post::with(['user','tags'])->findOrFail($id);

        return new PostResource($post);

    }


    public function search(Request $request)
    {
       $posts= Post::where
         ('description','like','%'.$request->search.'%')->
         orwhere('title','like','%'.$request->search.'%')->paginate(15);
        return view('posts.search',['posts'=>$posts]);
    }
public function apisearch(Request $request)
    {
       $posts= Post::with(['user','tags'])
       ->where
         ('description','like','%'.$request->search.'%')->
         orwhere('title','like','%'.$request->search.'%')->paginate(15);
        return PostResource::collection($posts)->additional([
           'status'=>'success',
           'total'=>$posts->count(),

        ]);
    }




    public function index()
    {
         $posts = Post::orderby('id','desc')->paginate(15);
        return view('posts.index',['posts'=>$posts]);
    }
     public function apiindex()
    {
         $posts = Post::orderby('id','desc')->paginate(15);
        return PostResource::collection($posts);
    }


     public function home()
    {
        $posts= Post::orderby('id','desc')->paginate(15);
        return view('home',['posts'=>$posts]);
    }
     public function apihome()
    {
        $posts= Post::orderby('id','desc')->paginate(15);
        return PostResource::collection($posts);

    }


    public function create()
    {
        Gate::authorize('create-post');
        $tags=Tag::select('id','name')->get();
        return view('posts.add',compact('tags') );
    }
     public function apicreate()
    {
        $tags=Tag::select('id','name')->get();
        return TagResourse::collection($tags);

    }

    public function edit($id)
    {
        $post=Post::findorfail($id);
        $tags=Tag::select('id','name')->get();
        $users=User::select('id','name')->get();
        return view('posts.edit',['post'=>$post,'tags'=>$tags,'users'=>$users ]);

    }
    public function apiedit($id)
    {
        $post=Post::findorfail($id);
        $tags=Tag::select('id','name')->get();
        $users=User::select('id','name')->get();
        return response()->json([

            'post'=> new PostResource($post),
            'users'=>UserResource::collection($users),
            'tags'=>TagResourse::collection($tags),

        ],200);
    }



     public function update($id ,UpdatePostRequest $request)
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

        return redirect('posts')->with('success', 'Post updated successfully');
       // return view('posts.edit',['post'=>$post]);
    }


 public function apiupdate($id ,UpdatePostRequest $request)
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
                'tags' => TagResourse::collection($post->tags),
           ], 200);
    }

public function store(StorePostRequest $request)
    {
        Gate::authorize('create-post');
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
        return redirect()->route('posts.index')->with('success', 'تم إنشاء البوست بنجاح ✅');
    }
    public function apistore(StorePostRequest $request)
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
                'tags' => TagResourse::collection($post->tags),
           ], 200 );
    }


    public function destroy($id)
    {

        $post=Post::findorfail($id);
        $post->delete();
        return redirect()->back()->with('success', 'Post deleted successfully!');
    }
     public function apidestroy($id)
    {

        $post=Post::findorfail($id);
        $post->delete();
       return response()->json([
        'message' => 'Post deleted successfully '
    ], 200);
    }
}
