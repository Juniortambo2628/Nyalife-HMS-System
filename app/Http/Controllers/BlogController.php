<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'blogs' => Blog::with('author')->where('is_published', true)->latest()->get()
        ]);
    }

    public function manage()
    {
        return Inertia::render('Blog/Index', [
            'blogs' => Blog::with('author')->latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Form');
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        return Inertia::render('Blog/Form', [
            'blog' => $blog
        ]);
    }

    public function store(StoreBlogRequest $request)
    {
        $validated = $request->validated();
        $imagePath = $request->file('image') ? $request->file('image')->store('blogs', 'public') : null;

        Blog::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . rand(1000, 9999),
            'content' => $validated['content'],
            'excerpt' => $validated['excerpt'],
            'tags' => $validated['tags'],
            'image_path' => $imagePath,
            'author_id' => auth()->id(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        return redirect()->route('blog.manage')->with('success', 'Blog post created successfully.');
    }

    public function update(UpdateBlogRequest $request, $id)
    {
        $blog = Blog::findOrFail($id);
        $validated = $request->validated();
        if ($request->file('image')) {
            $validated['image_path'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($validated);

        return redirect()->route('blog.manage')->with('success', 'Blog post updated successfully.');
    }

    public function destroy($id)
    {
        Blog::findOrFail($id)->delete();
        return back()->with('success', 'Blog post deleted.');
    }
}
