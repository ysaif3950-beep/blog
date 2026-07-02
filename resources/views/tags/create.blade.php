@extends('layout.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <!-- Header -->
        <div class="mb-4">
            <h1 style="font-size: var(--text-3xl); font-weight: 700;">Create New Tag</h1>
            <p class="text-muted" style="font-size: var(--text-base);">Add a category label to organize your blog posts</p>
        </div>

        @include('layout.error')
        @include('layout.message')

        <!-- Form Card -->
        <div class="card">
            <div class="card-body" style="padding: var(--space-6);">
                <form action="{{ route('tags.store') }}" method="post">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-5">
                        <label class="form-label" for="tag-name">
                            Tag Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="tag-name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Write a clear tag name"
                               required
                               autofocus>
                        <div class="form-text">Use short names that make posts easier to find</div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-3 pt-4 border-top">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i>
                            Save Tag
                        </button>
                        <a href="{{ route('tags.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
