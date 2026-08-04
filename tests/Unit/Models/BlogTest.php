<?php

namespace Tests\Unit\Models;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_has_fillable_attributes(): void
    {
        $author = User::factory()->create();
        $blog = Blog::create([
            'title' => 'Understanding IUDs',
            'slug' => 'understanding-iuds-1234',
            'content' => 'A comprehensive guide to intrauterine devices.',
            'excerpt' => 'Guide to IUDs',
            'author_id' => $author->user_id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertSame('Understanding IUDs', $blog->title);
        $this->assertSame('understanding-iuds-1234', $blog->slug);
        $this->assertTrue((bool) $blog->is_published);
    }

    public function test_blog_belongs_to_author(): void
    {
        $author = User::factory()->create();
        $blog = Blog::create([
            'title' => 'Test Blog',
            'slug' => 'test-blog-1234',
            'content' => 'Content here.',
            'author_id' => $author->user_id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertNotNull($blog->author);
        $this->assertSame($author->user_id, $blog->author->user_id);
    }

    public function test_blog_published_vs_draft(): void
    {
        $author = User::factory()->create();

        Blog::create([
            'title' => 'Published Blog Unique '.uniqid(),
            'slug' => 'published-'.uniqid(),
            'content' => 'Content',
            'author_id' => $author->user_id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $publishedCount = Blog::where('is_published', true)->count();
        $this->assertGreaterThanOrEqual(1, $publishedCount);

        $draftBlog = Blog::create([
            'title' => 'Draft Blog Unique '.uniqid(),
            'slug' => 'draft-'.uniqid(),
            'content' => 'Content',
            'author_id' => $author->user_id,
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->assertFalse((bool) $draftBlog->is_published);
        $this->assertNull($draftBlog->published_at);
    }
}
