<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();
        $authorId = $admin ? $admin->user_id : 1;

        $posts = [
            [
                'title' => 'Understanding Prenatal Care: A Guide for Expectant Mothers',
                'excerpt' => 'Prenatal care is essential for a healthy pregnancy. Learn what to expect during your visits.',
                'content' => 'Regular prenatal care is one of the best ways to ensure a healthy pregnancy. Your healthcare provider will monitor your health and the developing baby\'s health throughout the pregnancy. During these visits, you\'ll receive important information about nutrition, physical activity, and what to expect during childbirth.',
                'image_path' => '/assets/img/service-tabs/Pregnancy-test-2.jpg',
                'tags' => ['Pregnancy', 'Prenatal', 'Health'],
            ],
            [
                'title' => 'The Importance of Regular Gynecological Screenings',
                'excerpt' => 'Early detection is key. Discover why annual check-ups are vital for women\'s health.',
                'content' => 'Annual gynecological exams are a crucial part of preventive healthcare for women. These screenings can detect issues early, when they are most treatable. From Pap smears to breast exams, staying up-to-date with your screenings is a proactive step in managing your long-term health.',
                'image_path' => '/assets/img/service-tabs/Obstetrics-care.jpg',
                'tags' => ['Screening', 'Wellness', 'Prevention'],
            ],
            [
                'title' => 'Managing Menopause: Tips and Strategies',
                'excerpt' => 'Menopause is a natural transition. Learn how to manage symptoms and thrive.',
                'content' => 'Menopause is a significant life transition for women. While it can bring various symptoms like hot flashes and night sweats, there are many ways to manage these changes effectively. Lifestyle adjustments, hormone therapy, and support from healthcare providers can help you navigate this period with confidence.',
                'image_path' => '/assets/img/service-tabs/Tampon.jpg',
                'tags' => ['Menopause', 'Womens Health', 'Lifestyle'],
            ],
            [
                'title' => 'Nyalife Women\'s Clinic: Your Partner in Reproductive Health',
                'excerpt' => 'We are dedicated to providing compassionate care for women at every stage of life.',
                'content' => 'At Nyalife Women\'s Clinic, we prioritize your reproductive health and well-being. Our team of experienced specialists is here to provide personalized care, from routine check-ups to advanced treatments. We believe in empowering women through education and support, ensuring you receive the best possible care.',
                'image_path' => '/assets/img/service-tabs/nyalife-1.JPG',
                'tags' => ['Nyalife', 'Care', 'Reproductive Health'],
            ],
        ];

        foreach ($posts as $post) {
            Blog::create(array_merge($post, [
                'slug' => Str::slug($post['title']) . '-' . rand(100, 999),
                'author_id' => $authorId,
                'is_published' => true,
                'published_at' => now(),
            ]));
        }
    }
}
