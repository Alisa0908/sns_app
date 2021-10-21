<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $width = 100;
        $height = 100;

        $file = $this->faker->image(null, $width, $height);
        $path = Storage::putFile('posts', $file);
        File::delete($file);
        return [
            'image' => basename($path),
            'post_id' => \App\Models\Post::Factory()->create(),
        ];
    }
}
