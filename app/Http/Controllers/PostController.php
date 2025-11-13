<?php

namespace App\Http\Controllers;

use App\Models\Post; // <-- ده الموديل
use App\Models\Tag;
use App\Http\Requests\StorePostRequest; // الريكويست
use App\Http\Requests\UpdatePostRequest;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PostController extends Controller
{
    public function show($id)
    {
        $post= Post::findorfail($id);

        return view('posts.show',['post'=>$post]);
    }


    public function search(Request $request)
    {
       $posts= Post::where
         ('description','like','%'.$request->search.'%')->
         orwhere('title','like','%'.$request->search.'%')->get();
        return view('posts.search',['posts'=>$posts]);
    }




    public function index()
    {
         $posts = Post::orderby('id','desc')->paginate(15);
        return view('posts.index',['posts'=>$posts]);
    }
     public function home()
    {
        $posts= Post::orderby('id','desc')->paginate(15);
        return view('home',['posts'=>$posts]);
    }

    public function create()
    {
        $users=User::select('id','name')->get();
        $tags=Tag::select('id','name')->get();
        return view('posts.add',compact('users','tags') );
    }

    public function edit($id)
    {
        $post=Post::findorfail($id);
        $tags=Tag::select('id','name')->get();
        return view('posts.edit',['post'=>$post,'tags'=>$tags]);

    }


     public function update($id ,UpdatePostRequest $request)
    {
        $post=Post::findorfail($id);
       $post->update($request->validated()); // بدلاً من Validated()
        return redirect('posts')->with('success', 'Post updated successfully');
       // return view('posts.edit',['post'=>$post]);
    }


public function store(StorePostRequest $request)
    {
        // التحقق من البيانات القادمة من الفورم
        $data = $request->validated();

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

    public function destroy($id)
    {

        $post=Post::findorfail($id);
        $post->delete();
        return redirect()->back()->with('success', 'Post deleted successfully!');
    }
}
