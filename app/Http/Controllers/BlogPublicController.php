<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogPublicController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with('author')->where('is_published', true)
            ->search($request->input('search'));

        // Tag Filter
        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->whereJsonContains('tags', $tag);
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        if ($sort === 'latest') {
            $query->latest();
        } elseif ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'alphabetical') {
            $query->orderBy('title', 'asc');
        }

        $blogs = $query->paginate(12)->withQueryString();

        // Get all unique tags for the sidebar
        $allTags = Blog::where('is_published', true)
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        return Inertia::render('Blog/PublicIndex', [
            'blogs' => $blogs,
            'filters' => (object) [
                'search' => $request->input('search', ''),
                'tag' => $request->input('tag', ''),
                'sort' => $request->input('sort', 'latest'),
            ],
            'allTags' => $allTags,
        ]);
    }

    public function show($slug)
    {
        $blog = Blog::with('author')->where('slug', $slug)->firstOrFail();
        
        return Inertia::render('Blog/Show', [
            'blog' => $blog
        ]);
    }
}
