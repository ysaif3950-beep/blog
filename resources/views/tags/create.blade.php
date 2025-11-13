@extends('layout.app')
@section('content')
<div class="col-12">
    <h1 class="p-3 border text-center my-3">Create Tag</h1>
</div>

<div class="col-8 mx-auto">
    <form action="{{ route('tags.store') }}" method="post" class="form border p-3">
        @csrf

        @include('layout.error')
        @include('layout.message')

        <div class="mb-3">
            <label class="form-label">Tag Name</label>
            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
        </div>

        

        

        <div class="mb-3">
            <button type="submit" class="btn btn-success w-100">Save</button>
        </div>
    </form>
</div>
@endsection
