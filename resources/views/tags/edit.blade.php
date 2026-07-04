@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="mb-4">
            <h1 style="font-size: var(--text-3xl); font-weight: 700;">Edit Tag</h1>
            <p class="text-muted" style="font-size: var(--text-base);">Update the tag name used to organize posts</p>
        </div>

        @include('layout.error')
        @include('layout.message')

        <div class="card">
            <div class="card-body" style="padding: var(--space-6);">
                <form action="{{ route('tags.update', $tag->id) }}" method="post">
                    @csrf
                    @method('put')

                    <div class="mb-5">
                        <label class="form-label" for="tag-name">
                            Tag Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="tag-name"
                               name="name"
                               value="{{ old('name', $tag->name) }}"
                               placeholder="Write a clear tag name"
                               required
                               autofocus>
                        <div class="form-text">Use short names that make posts easier to find</div>
                    </div>

                    <div class="d-flex gap-3 pt-4 border-top">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i>
                            Save Changes
                        </button>
                        <a href="{{ route('tags.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
