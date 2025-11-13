@extends('layout.app')
@section('content')
 <div class="col-12">
    <h1 class="p-3 border text-center my-3">Edit post info</h1>
</div>
            @include('layout.message')
            @include('layout.error')



<div class="col-8 mx-auto">
    <form action="{{ url('posts/'.$post->id) }}" method="post" class="form border p-3">
        @csrf
        @method('put')
        <div class="mb-3">
            <label for="">Post title</label>

            <input type="text" value="{{ $post->title}}" class="form-control" name="title">
        </div>

        <div class="mb-3">
            <label for="">Post Description</label>
            <textarea class="form-control" name="description"  rows="7">{{$post->description}}</textarea>
        </div>

        <div class="mb-3">
            <label for="">Writer</label>
            <select name="user_id" class="form-control">
                <option value="1">saif</option>
                <option value="2">yasser</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="">Tags</label>
            <select name="tags[]" class="form-control" multiple>
             @foreach ($tags as $tag)
                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
             @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for=""> Post Image</label>
            <input type="file" class="form-control" name="image" >
        </div>
        <div class="mb-3">
            <input type="submit" class="form-control bg-success text-white" value="Save">
        </div>
    </form>
</div>
@endsection
