<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate();
        return view('users.index', compact('users'));
    }
    public function create()
    {
        return view('users.create');
    }
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }
   public function edit($id)
{
    $user = User::findOrFail($id);
    return view('users.edit', compact('user'));
}

    public function update(UpdateUserRequest $request, User $user)
{
   $data = $request->validated();
    if($request->filled('password')){
        $data['password']=bcrypt($data['password']);
    }
        else{
             unset($data['password']);
        }



    $user->update($data);

    return redirect()->route('users.index')->with('success', 'User updated successfully!');
}



   public function destroy(User $user)
{
    $user->delete();
    return redirect()->route('users.index')->with('success', 'User deleted successfully.');
}

    public function posts(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.posts', compact('user'));
    }

}
