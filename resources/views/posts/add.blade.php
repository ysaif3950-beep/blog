@extends('layout.app')
@section('content')
 <div class="col-12">
    <h1 class="p-3 border text-center my-3">Add post</h1>
</div>

<div class="col-8 mx-auto">
    <form action="{{ url('posts') }}" method="post" class="form border p-3" enctype="multipart/form-data">
        @csrf

        @include('layout.error')
        @include('layout.message')

        <div class="mb-3">
            <label for="">Post title</label>
            <input type="text" class="form-control" name="title" value="{{old('title')}}">
        </div>

        <div class="mb-3">
            <label for="">Post Description</label>
            <textarea class="form-control" name="description" rows="7">{{ old('description')}}</textarea>
        </div>

        {{-- المستخدم الحالي محدد تلقائياً --}}
        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
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
