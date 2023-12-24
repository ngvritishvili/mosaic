<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Image;

class MosaicController extends Controller
{
    public function createMosaic(Request $request)
    {
        // Validate the form data (ensure you have 'main_photo' and 'batch_photos' fields in your form)
        $request->validate([
            'main_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:65536', // Adjust the validation rules as needed
            'batch_photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:65536',
        ]);

        // Get the main photo
        $mainPhoto = $request->file('main_photo');

        // Create a unique filename for the main photo
        $mainPhotoName = 'main_' . time() . '.' . $mainPhoto->extension();

        // Move the main photo to the public/uploads directory
        $mainPhoto->move(public_path('uploads'), $mainPhotoName);

        // Create a new Intervention Image instance for the main photo
        $mainImage = Image::make(public_path('uploads') . '/' . $mainPhotoName);

        // Loop through batch photos
        $mosaicImage = $mainImage; // Start with the main image
        foreach ($request->file('batch_photos') as $batchPhoto) {
            // Create a unique filename for each batch photo
            $batchPhotoName = 'batch_' . time() . '_' . mt_rand() . '.' . $batchPhoto->extension();

            // Move the batch photo to the public/uploads directory
            $batchPhoto->move(public_path('uploads'), $batchPhotoName);

            // Create a new Intervention Image instance for the batch photo
            $batchImage = Image::make(public_path('uploads') . '/' . $batchPhotoName);

            // Resize the batch photo to fit a portion of the main photo
            $batchImage->fit($mainImage->width() / 10, $mainImage->height() / 10);

            // Overlay the batch photo onto the mosaic image
            $mosaicImage->insert($batchImage, 'top-left', 0, 0);
        }

        // Save or return the mosaic image
        $mosaicImagePath = 'uploads/mosaic_' . time() . '.' . $mainPhoto->extension();
        $mosaicImage->save(public_path($mosaicImagePath));

        return view('welcome', ['mosaicImagePath' => $mosaicImagePath]);
    }

    private function generateMosaic($mainImage, $imageSet)
    {
        // Create Intervention Image instances for main image and each tile
        $mainImage = Image::make($mainImage);
        $tileImages = collect();

        foreach ($imageSet as $image) {
            $tileImages->push(Image::make($image));
        }

        // Get dimensions of main image and calculate tile size
        $mainWidth = $mainImage->width();
        $mainHeight = $mainImage->height();
        $tileWidth = $mainWidth / count($tileImages);
        $tileHeight = $mainHeight / count($tileImages);

        // Create a new blank canvas for the mosaic
        $mosaic = Image::canvas($mainWidth, $mainHeight);

        // Iterate through each tile position and paste the corresponding image
        foreach ($tileImages as $index => $tileImage) {
            $x = $index * $tileWidth;
            $y = 0; // You may adjust this based on your mosaic layout

            // Resize the tile image to match the size of each tile
            $tileImage->resize($tileWidth, $tileHeight);

            // Paste the tile image onto the mosaic canvas
            $mosaic->insert($tileImage, 'top-left', $x, $y);
        }

        return $mosaic;
    }

    public function cutImage(Request $request)
    {
        // Load the original image
        $originalImage = imagecreatefromjpeg($request->file('img'));

// Get the dimensions of the original image
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

// Calculate the width and height of each piece
        $pieceWidth = $originalWidth / 100;  // Assuming 10 columns
        $pieceHeight = $originalHeight / 100; // Assuming 10 rows
//        dd($originalWidth,$originalHeight,$pieceWidth,$pieceHeight);
// Loop through rows
        for ($row = 0; $row < 100; $row++) {
            // Loop through columns
            for ($col = 0; $col < 100; $col++) {
                // Create a new image for each piece
                $piece = imagecreatetruecolor($pieceWidth, $pieceHeight);

                // Copy the corresponding portion from the original image
                imagecopy($piece, $originalImage, 0, 0, $col * $pieceWidth, $row * $pieceHeight, $pieceWidth, $pieceHeight);

                // Save or output the individual piece (adjust as needed)
                $pieceFileName =  'storage/images/pieces/piece_' . $row . '_' . $col . '.jpg' ;

                $fie = imagejpeg($piece, $pieceFileName);


                // Destroy the piece to free up memory
                imagedestroy($piece);
            }
        }

// Destroy the original image
        imagedestroy($originalImage);

    }

    public function show()
    {
        return view('cutimage');
    }

    private function calculateRGBPercentage($imagePath)
    {
        // Open the image using Intervention Image
        $img = Image::make($imagePath);

        // Get the image size
        $width = $img->width();
        $height = $img->height();

        // Initialize counters for RGB values
        $totalPixels = $width * $height;
        $totalRed = 0;
        $totalGreen = 0;
        $totalBlue = 0;

        // Iterate over each pixel and accumulate RGB values
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($img->getCore(), $x, $y);
                $colors = imagecolorsforindex($img->getCore(), $rgb);

                $totalRed += $colors['red'];
                $totalGreen += $colors['green'];
                $totalBlue += $colors['blue'];
            }
        }

        // Calculate percentages
        $percentageRed = ($totalRed / ($totalPixels * 255)) * 100;
        $percentageGreen = ($totalGreen / ($totalPixels * 255)) * 100;
        $percentageBlue = ($totalBlue / ($totalPixels * 255)) * 100;

        return [
            'red' => $percentageRed,
            'green' => $percentageGreen,
            'blue' => $percentageBlue,
        ];
    }
}
