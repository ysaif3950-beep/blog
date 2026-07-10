<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    //
    public function index(): View
    {
        $users = User::orderBy('id', 'desc')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['password'] = bcrypt($data['password']);

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request
                ->file('profile_image')
                ->store('profiles', 'public');
        }

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit-profile', [
            'user' => $user,
            'formAction' => route('users.update', $user),
            'cancelUrl' => route('users.index'),
            'pageTitle' => 'Edit Profile',
            'pageDescription' => 'Update profile details, photo, and access level',
            'submitLabel' => 'Save Changes',
            'showRoleField' => true,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['profile_image']);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function posts(string $id): View
    {
        $user = User::findOrFail($id);

        return view('users.posts', compact('user'));
    }

    public function profile(): View
    {
        $user = User::findOrFail(auth()->id());
        $posts = $user->posts()
            ->with('tags')
            ->latest()
            ->paginate(6, ['*'], 'posts_page');
        $tags = $user->tags()
            ->withCount('posts')
            ->latest()
            ->paginate(12, ['*'], 'tags_page');

        return view('users.profile', compact('user', 'posts', 'tags'));
    }

    public function editProfile(): View
    {
        $user = User::findOrFail(auth()->id());

        return view('users.edit-profile', [
            'user' => $user,
            'formAction' => route('users.profile.update'),
            'cancelUrl' => route('users.profile'),
            'pageTitle' => 'Edit Profile',
            'pageDescription' => 'Update your profile details and photo',
            'submitLabel' => 'Save Profile',
            'showRoleField' => false,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = User::findOrFail(auth()->id());
        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['profile_image']);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->update($data);

        return redirect()->route('users.profile')->with('success', 'Profile updated successfully.');
    }
}
