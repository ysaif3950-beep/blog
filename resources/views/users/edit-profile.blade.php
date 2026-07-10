@extends('layout.app')

@section('content')
@php
    $formAction ??= route('users.profile.update');
    $cancelUrl ??= route('users.profile');
    $pageTitle ??= 'Edit Profile';
    $pageDescription ??= 'Update your profile details and photo';
    $submitLabel ??= 'Save Profile';
    $showRoleField ??= false;
@endphp

<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="mb-4">
            <h1 style="font-size: var(--text-3xl); font-weight: 700;">{{ $pageTitle }}</h1>
            <p class="text-muted" style="font-size: var(--text-base);">{{ $pageDescription }}</p>
        </div>

        @include('layout.message')
        @include('layout.error')

        <div class="card">
            <div class="card-body" style="padding: var(--space-6);">
                <form action="{{ $formAction }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')

                    <div class="d-flex align-items-center gap-4 mb-5">
                        <img src="{{ $user->profile_image_url }}"
                             alt="{{ $user->name }}"
                             class="rounded-circle object-fit-cover"
                             style="width: 96px; height: 96px; border: 4px solid var(--primary-50);">

                        <div class="flex-grow-1">
                            <label class="form-label" for="profile-image">Profile Image</label>
                            <input type="file"
                                   class="form-control"
                                   id="profile-image"
                                   name="profile_image"
                                   accept="image/png,image/jpeg,image/jpg,image/gif">
                            <div class="form-text">JPG, PNG, or GIF up to 2MB</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="user-name">
                            Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="user-name"
                               name="name"
                               value="{{ old('name', $user->name) }}"
                               placeholder="Enter your full name"
                               required
                               autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="user-email">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               class="form-control"
                               id="user-email"
                               name="email"
                               value="{{ old('email', $user->email) }}"
                               placeholder="name@example.com"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="user-password">Password</label>
                        <input type="password"
                               class="form-control"
                               id="user-password"
                               name="password"
                               placeholder="Enter a new password">
                        <div class="form-text">Leave it blank to keep the current password</div>
                    </div>

                    <div class="{{ $showRoleField ? 'mb-4' : 'mb-5' }}">
                        <label class="form-label" for="user-password-confirmation">Confirm Password</label>
                        <input type="password"
                               class="form-control"
                               id="user-password-confirmation"
                               name="password_confirmation"
                               placeholder="Repeat the new password">
                    </div>

                    @if ($showRoleField)
                        <div class="mb-5">
                            <label class="form-label" for="user-role">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="user-role" class="form-select" required>
                                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                                <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                            </select>
                            <div class="form-text">Choose the access level for this account</div>
                        </div>
                    @endif

                    <div class="d-flex gap-3 pt-4 border-top">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i>
                            {{ $submitLabel }}
                        </button>
                        <a href="{{ $cancelUrl }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
