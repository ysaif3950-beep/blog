<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tag;
class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $tags=tag::paginate(15);
        return view('tags.index',compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
        ]);
        tag::create($data);
        return redirect()->route('tags.index')->with('success','Tag created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $tag = tag::findOrFail($id);
        return view('tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, tag $tag)
    {
        //

        $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
        ]);
        $tag->update($data);
        return redirect()->route('tags.index')->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(tag $tag)
    {
        //
        $tag->delete();
        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully.');
    }
}
