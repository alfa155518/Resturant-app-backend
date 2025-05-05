<?php

namespace App\Jobs;

use App\Helpers\handelUploadPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteUserAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $publicId;

    /**
     * Create a new job instance.
     *
     * @param string $publicId The Cloudinary public ID of the avatar to delete
     * @return void
     */
    public function __construct($publicId)
    {
        $this->publicId = $publicId;
    }

    /**
     * Execute the job.
     *
     * @param handelUploadPhoto $uploadHandler
     * @return void
     */
    public function handle(handelUploadPhoto $uploadHandler)
    {
        $uploadHandler->deletePhoto($this->publicId);
    }
}