@extends('layout.app')
@section('content')
 <div class="col-12">
    <h1 class="p-3 border text-center my-3">All posts</h1>
     </div>
  @foreach ($posts as $post)

<div class="col-12"></div>

     <div class="card">
                    <div class="card-header">
    {{$post->user->name}} -{{$post->created_at->format('Y-m-d')}}
</div>
  <div class="card-body">
    <h5 class="card-title">{{$post->title}}</h5>
        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" width="100" class="img-thumbnail mb-3">
    <p class="card-text">{{ \Str::limit($post->description,50)}}</p>
    <a href="{{url('posts/' .$post->id)}}" class="btn btn-primary">Show post</a>
  </div>
           </div>
  @endforeach
<div class="col-12">
          {{ $posts->links() }}
        </div>
@endsection
