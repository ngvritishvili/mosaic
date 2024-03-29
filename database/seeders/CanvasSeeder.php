<?php

namespace Database\Seeders;

use App\Enums\Resolution;
use App\Models\Canvas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CanvasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        self::blackCanvas();
        self::whiteCanvas();
    }

    private function blackCanvas()
    {
        // Create a canvas image

        foreach (Resolution::cases() as $value) {
            $resol = $this->resolutionGenerate($value->name);

            $canvas = imagecreatetruecolor($resol['width'], $resol['height']);

            $filename = $value->name . '_black_canvas.jpg'; // Set your desired filename and extension
            $storagePath = 'storage/images/canvases/' . $filename; // Define the storage path

// Save the image to storage

// Set canvas background color (optional)
            $backgroundColor = imagecolorallocate($canvas, 0, 0, 0);
            imagefill($canvas, 0, 0, $backgroundColor);
            imagejpeg($canvas, public_path($storagePath));
            imagedestroy($canvas);

            Canvas::updateOrCreate(
                [
                    'resolution' => $value->value,
                    'color' => 'black'
                ],
                [
                    'name' => $filename,
                    'path' => $storagePath,
                    'resolution' => $value->value,
                    'color' => 'black'
                ]);
        }
    }

    private function whiteCanvas()
    {
        // Create a canvas image

        foreach (Resolution::cases() as $value) {
            $resol = $this->resolutionGenerate($value->name);

            $canvas = imagecreatetruecolor($resol['width'], $resol['height']);

            $filename = $value->name . '_white_canvas.jpg'; // Set your desired filename and extension
            $storagePath = 'storage/images/canvases/' . $filename; // Define the storage path

// Save the image to storage

// Set canvas background color (optional)
            $backgroundColor = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $backgroundColor);
            imagejpeg($canvas, public_path($storagePath));
            imagedestroy($canvas);

            Canvas::updateOrCreate(
                [
                    'resolution' => $value->value,
                    'color' => 'white'
                ],
                [
                    'name' => $filename,
                    'path' => $storagePath,
                    'resolution' => $value->value,
                    'color' => 'white',
                ]);
        }
    }

    private function resolutionGenerate($resolution)
    {
        switch ($resolution) {
            case 'RHD':
                return ['width' => '1280', 'height' => '720'];
            case 'RFHD':
                return ['width' => '1920', 'height' => '1080'];
            case 'R2K':
                return ['width' => '2048', 'height' => '1080'];
            case 'R4K':
                return ['width' => '3840', 'height' => '2160'];
            case 'R8K':
                return ['width' => '7680', 'height' => '4320'];
            case 'R12K':
                return ['width' => '12288', 'height' => '6440'];
        }
    }

}
