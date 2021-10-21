<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getImagePathAttribute()
    {
        return 'posts/' . $this->images[0]->image;
    }

    public function getImageUrlAttribute()
    {
        if (config('filesystems.default') == 'gcs') {
            return Storage::temporaryUrl($this->image_path, now()->addMinutes(5));
        }
        return Storage::url($this->image_path);
    }

    public function getImagePathsAttribute()
    {
        $paths = [];
        foreach ($this->images as $image) {
            $paths[] = 'posts/' . $image->image;
        }
        return $paths;
    }

    public function getImageUrlsAttribute()
    {
        $urls = [];
        foreach ($this->image_paths as $path) {
            $urls[] = Storage::url($path);
        }
        return $urls;
    }
}
