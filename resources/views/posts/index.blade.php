@extends('layout.app')
@section('content')
    <div class="col-12">
        <a href="{{url('posts/create')}}" class="btn btn-primary my-3">Add New Post</a>
        <h1 class="p-3 border text-center my-3">All posts</h1>
    </div>

    <div class="col-12">
        @include('layout.message')
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Writer</th>
                    <th>Tags</th>
                    <th>Image</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posts as $post)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{$post->title}}</td>
                    <td>{{$post->description}}</td>
                    <td>{{ $post->user->name ?? 'No user' }}</td>
                    <td> 
                    @forelse ($post->tags as $tag)
              <span class="badge bg-primary me-1">{{ $tag->name }}</span>
  
  
                 @empty              
                    <span class="text-muted">No tags</span>
                @endforelse
                    </td>
                    <td>
                        <!-- ✅ هنا التعديل -->
                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" width="100" class="img-thumbnail">
                    </td>
                    <td>
                        <a href="{{url('posts/' .$post->id. '/edit')}}" class="btn btn-info btn-sm">Edit</a>
                    </td>
                    <td>
                        <form action="{{url('posts/'.$post->id)}}" method="post" class="d-inline">
                            @method('Delete')
                            @csrf
                            <input type="submit" value="Delete" class="btn btn-danger btn-sm"
                                   onclick="return confirm('هل أنت متأكد من الحذف؟')">
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="col-12">
        {{ $posts->links() }}
    </div>
@endsection
