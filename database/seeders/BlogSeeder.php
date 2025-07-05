<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing blog data
        DB::table('blogs')->truncate();

        // Get the path to the JSON file
        $json = File::get(database_path('seeders/data/blogs.json'));
        $blogs = json_decode($json, true);

        foreach ($blogs as $blogData) {
            // Format the published date
            $publishedAt = isset($blogData['publishDate']) 
                ? Carbon::createFromFormat('Y-m-d', $blogData['publishDate'])->format('Y-m-d H:i:s')
                : now()->format('Y-m-d H:i:s');

            // Prepare tags and comments as JSON
            $tags = json_encode($blogData['tags'] ?? []);
            $comments = json_encode($this->transformComments($blogData['comments'] ?? []));
            
            // Insert using raw SQL to avoid any date formatting issues
            DB::table('blogs')->insert([
                'title' => $blogData['title'] ?? 'Untitled Blog Post',
                'excerpt' => $blogData['excerpt'] ?? '',
                'content' => $blogData['content'] ?? '',
                'image' => $blogData['image'] ?? null,
                'author_name' => $blogData['author'] ?? 'Anonymous',
                'published_at' => $publishedAt,
                'category' => $blogData['category'] ?? 'Uncategorized',
                'status' => strtolower($blogData['status'] ?? 'draft'),
                'tags' => $tags,
                'comments' => $comments,
                'likes' => '[]',
                'dislikes' => '[]',
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $this->command->info('Successfully seeded blog posts!');
    }

    /**
     * Transform comments to the required format
     */
    protected function transformComments(array $comments): array
    {
        return array_map(function ($comment) {
            return [
                'id' => $comment['id'] ?? uniqid(),
                'name' => $comment['name'] ?? 'Anonymous',
                'content' => $comment['content'] ?? '',
                'date' => $comment['date'] ?? now()->toIso8601String(),
            ];
        }, $comments);
    }
}
