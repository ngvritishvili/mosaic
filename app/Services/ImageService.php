<?php

namespace App\Services;

use App\Enums\Resolution;
use App\Http\Requests\CanvasStoreRequest;
use App\Models\Canvas;
use App\Models\Category;
use App\Models\ImagesLibrary;
use App\Models\MainImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use JetBrains\PhpStorm\NoReturn;
use function Sodium\compare;

class ImageService
{

    public function index()
    {
        return view('welcome');
    }

    public function createMosaic(Request $request)
    {
        // Validate the form data (ensure you have 'main_photo' and 'batch_photos' fields in your form)
        $request->validate([
            'main_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10536', // Adjust the validation rules as needed
            'batch_photos.*' => 'required|image|mimes:jpeg,png,jpg|max:10536',
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
        $this->cutImage($mainImage);

        return;
    }

    public function cutImage(Request $request)
    {
        // Load the original image
        $originalImage = imagecreatefromjpeg($request->file('img'));

// Get the dimensions of the original image
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        $widthPieceCount = 60;
        $heightPieceCount = 60;
// Calculate the width and height of each piece
        $pieceWidth = $originalWidth / $widthPieceCount;  // Assuming 60 columns
        $pieceHeight = $originalHeight / $heightPieceCount; // Assuming 50 rows
//        dd($originalWidth,$originalHeight,$pieceWidth,$pieceHeight);
// Loop through rows
        for ($row = 0; $row < $widthPieceCount; $row++) {
            // Loop through columns
            for ($col = 0; $col < $originalHeight; $col++) {
                // Create a new image for each piece
                $piece = imagecreatetruecolor($pieceWidth, $pieceHeight);

                // Copy the corresponding portion from the original image
                imagecopy($piece, $originalImage, 0, 0, $col * $pieceWidth, $row * $pieceHeight, $pieceWidth, $pieceHeight);

                // Save or output the individual piece (adjust as needed)
                $pieceFileName = 'storage/images/pieces/piece_' . $row . '_' . $col . '.jpg';

                $file = imagejpeg($piece, $pieceFileName);

                $imageData = self::checkImgCountPixel([$file]);
                // Move the batch photo to the public/uploads directory
                $file->move(public_path('storage/images/main_pieces'), $pieceFileName);

                MainImage::create([
                    'position_x' => $pieceFileName,
                    'position_y' => 'storage/images/main_pieces/' . $pieceFileName,
                    'resolution' => Resolution::findById($request->resolution),
                    'dark_range' => $imageData['dark_range'],
                    'medium_range' => $imageData['medium_range'],
                    'light_range' => $imageData['light_range'],
                ]);


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

    public function countPixelColors($images = [])
    {
        $data = [];
        foreach ($images as $key => $pathToImage) {

            // Load the image (assuming it's a JPEG, adjust accordingly)
            $image = imagecreatefromjpeg($pathToImage);

// Get the image dimensions
            $width = imagesx($image);
            $height = imagesy($image);

// Initialize counters for each RGB range
            $countRange1 = 0;
            $countRange2 = 0;
            $countRange3 = 0;

// Define RGB ranges (adjust based on your requirements)
            $range1 = ['min' => [0, 0, 0], 'max' => [85, 85, 85]];      // Example: Dark
            $range2 = ['min' => [86, 86, 86], 'max' => [170, 170, 170]]; // Example: Medium
            $range3 = ['min' => [171, 171, 171], 'max' => [255, 255, 255]]; // Example: Light

// Loop through each pixel in the image
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    // Get RGB values of the current pixel
                    $rgb = imagecolorat($image, $x, $y);
                    $color = imagecolorsforindex($image, $rgb);

                    // Check if the pixel falls within any of the defined ranges
                    if (
                        $this->isWithinRange($color, $range1) ||
                        $this->isWithinRange($color, $range2) ||
                        $this->isWithinRange($color, $range3)
                    ) {
                        // Increment the counter for the corresponding range
                        if ($this->isWithinRange($color, $range1)) {
                            $countRange1++;
                        } elseif ($this->isWithinRange($color, $range2)) {
                            $countRange2++;
                        } elseif ($this->isWithinRange($color, $range3)) {
                            $countRange3++;
                        }
                    }
                }
            }

// Calculate percentages
            $totalPixels = $width * $height;
            $percentageRange1 = ($countRange1 / $totalPixels) * 100;
            $percentageRange2 = ($countRange2 / $totalPixels) * 100;
            $percentageRange3 = ($countRange3 / $totalPixels) * 100;

// Free up memory
            imagedestroy($image);

            $data[] = [
                'position' => $key + 1,
                'dark_range' => round($percentageRange1, 2),
                'medium_range' => round($percentageRange2, 2),
                'light_range' => round($percentageRange3, 2),
            ];

        }

        return $data;

    }

    // Helper function to check if an RGB value falls within a specified range
    private function isWithinRange($rgb, $range)
    {
        return (
            $rgb['red'] >= $range['min'][0] && $rgb['red'] <= $range['max'][0] &&
            $rgb['green'] >= $range['min'][1] && $rgb['green'] <= $range['max'][1] &&
            $rgb['blue'] >= $range['min'][2] && $rgb['blue'] <= $range['max'][2]
        );
    }

    private function saveTemporary($data = [])
    {
        DB::table('temporary_main_pieces')->insert(
            $data
        );

        return $this;
    }

    public function getAllPieces()
    {
        // Path to the images folder
        $folderPath = public_path('storage/images/pieces');

        // Get all files in the folder
        $files = File::files($folderPath);

        // Filter only image files (you may customize this based on your image file extensions)
        $imageFiles = array_filter($files, function ($file) {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($extension, $allowedExtensions);
        });

        // Map the file paths to URLs using the asset function
        $imageUrls = array_map(function ($file) {
            return public_path('storage/images/pieces/' . basename($file));
        }, $imageFiles);

        return $imageUrls;
    }

    public function importBatchPhotos(Request $request): RedirectResponse
    {
        set_time_limit(config('app.max_execution_time'));

        foreach ($request->file('batch_photos') as $key => $batchPhoto) {
            // Create a unique filename for each batch photo
            $batchPhotoName = 'none' . '_' . time() . '.' . $batchPhoto->extension();

            $imageData = self::checkImgCountPixel([$batchPhoto]);
            // Move the batch photo to the public/uploads directory
            $batchPhoto->move(public_path('storage/images/library'), $batchPhotoName);

            ImagesLibrary::create([
                'filename' => $batchPhotoName,
                'path' => 'storage/images/library/' . $batchPhotoName,
                'category_id' => $request->category_id ?? Category::where('name', 'none')->first()->id,
                'dark_range' => $imageData['dark_range'],
                'medium_range' => $imageData['medium_range'],
                'light_range' => $imageData['light_range'],
            ]);
        }

        return back()->with('message', 'Successfully Imported!');
    }

    public function importImg()
    {
        return view('import_image');
    }

    private function checkImgCountPixel($file)
    {
        // Load the image (assuming it's a JPEG, adjust accordingly)
        $image = imagecreatefromjpeg($file[0]);

        // Get the image dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Initialize counters for each RGB range
        $countRange1 = 0;
        $countRange2 = 0;
        $countRange3 = 0;

        // Define RGB ranges (adjust based on your requirements)
        $range1 = ['min' => [0, 0, 0], 'max' => [85, 85, 85]];      // Example: Dark
        $range2 = ['min' => [86, 86, 86], 'max' => [170, 170, 170]]; // Example: Medium
        $range3 = ['min' => [171, 171, 171], 'max' => [255, 255, 255]]; // Example: Light

        // Loop through each pixel in the image
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                // Get RGB values of the current pixel
                $rgb = imagecolorat($image, $x, $y);
                $color = imagecolorsforindex($image, $rgb);

                // Check if the pixel falls within any of the defined ranges
                if (
                    $this->isWithinRange($color, $range1) ||
                    $this->isWithinRange($color, $range2) ||
                    $this->isWithinRange($color, $range3)
                ) {
                    // Increment the counter for the corresponding range
                    if ($this->isWithinRange($color, $range1)) {
                        $countRange1++;
                    } elseif ($this->isWithinRange($color, $range2)) {
                        $countRange2++;
                    } elseif ($this->isWithinRange($color, $range3)) {
                        $countRange3++;
                    }
                }
            }
        }

        // Calculate percentages
        $totalPixels = $width * $height;
        $percentageRange1 = ($countRange1 / $totalPixels) * 100;
        $percentageRange2 = ($countRange2 / $totalPixels) * 100;
        $percentageRange3 = ($countRange3 / $totalPixels) * 100;

        // Free up memory
        imagedestroy($image);

        $data = [
            //                'position' => $key + 1,
            'dark_range' => round($percentageRange1, 2),
            'medium_range' => round($percentageRange2, 2),
            'light_range' => round($percentageRange3, 2),
        ];

        return $data;
    }

    public function createCanvas(CanvasStoreRequest $request)
    {
        // Create a canvas image
        $canvasWidth = 4111;
        $canvasHeight = 4220;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        $filename = 'canvas.jpg'; // Set your desired filename and extension
        $storagePath = 'storage/images/canvases/' . $filename; // Define the storage path

// Save the image to storage


// Set canvas background color (optional)
        $backgroundColor = imagecolorallocate($canvas, 0, 0, 0);
        imagefill($canvas, 0, 0, $backgroundColor);

        imagejpeg($canvas, $storagePath);
        imagedestroy($canvas);

        Canvas::create([
            'name' => $filename,
            'path' => $storagePath,
        ]);

        return view('welcome', compact('storagePath'));

// Load and add the first image to the canvas
        $image1 = imagecreatefromjpeg(public_path('path/to/first/image.jpg'));
        list($width1, $height1) = getimagesize('path/to/first/image.jpg');
        imagecopy($canvas, $image1, 0, 0, 0, 0, $width1, $height1);

// Load and add the second image to the canvas
        $image2 = imagecreatefromjpeg(public_path('path/to/second/image.jpg'));
        list($width2, $height2) = getimagesize('path/to/second/image.jpg');
        imagecopy($canvas, $image2, $canvasWidth - $width2, 0, 0, 0, $width2, $height2);

// Save or display the final canvas image
        imagejpeg($canvas, public_path('path/to/save/final/canvas.jpg'));

// Free up memory
        imagedestroy($canvas);
        imagedestroy($image1);
        imagedestroy($image2);
    }

    private function resizeImage($image, $newWidth = 300, $newHeight = 200)
    {
// Path to the original image
        $originalImagePath = public_path('path/to/original/image.jpg');

// Load the original image
        $originalImage = imagecreatefromjpeg($originalImagePath);

// Get the original image dimensions
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

// Create a new canvas for the resized image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

// Resize the original image to fit the new canvas
        imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

// Save or display the resized image
        $resizedImagePath = public_path('path/to/save/resized/image.jpg');
        imagejpeg($resizedImage, $resizedImagePath);

// Free up memory
        imagedestroy($originalImage);
        imagedestroy($resizedImage);

        return $resizedImage;

    }

    public function cropImage()
    {
        $image = public_path('storage/testing_imgs/123.jpg');
        // Load the original image
        $originalImage = imagecreatefromjpeg($image);
//        $originalImage = imagecreatefromjpeg('path/to/original/image.jpg');



// Get the dimensions of the original image
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        dd($originalWidth,$originalHeight);
// Set the coordinates and dimensions for the crop
        $cropX = 150;    // X-coordinate of the top-left corner of the crop
        $cropY = 150;    // Y-coordinate of the top-left corner of the crop
        $cropWidth = 900;  // Width of the crop
        $cropHeight = 900; // Height of the crop

//        // Calculate the dimensions for the square crop
//        $cropSize = min($originalWidth, $originalHeight); // Use the smaller dimension
//        $cropX = ($originalWidth - $cropSize) / 2;
//        $cropY = ($originalHeight - $cropSize) / 2;

// Create a new image resource for the cropped image
        $croppedImage = imagecrop($originalImage, [
            'x' => $cropX,
            'y' => $cropY,
            'width' => $cropWidth,
            'height' => $cropHeight,
        ]);

        if ($croppedImage !== false) {
            // Save or output the cropped image
            imagejpeg($croppedImage, public_path('storage/images/cropped/img_cropped_old.jpg'));

            // Free up memory by destroying the image resources
            imagedestroy($originalImage);
            imagedestroy($croppedImage);
        } else {
            // Error handling if cropping fails
            echo "Image cropping failed.";
        }


    }
    public function clearFolder($path, $prefix)
    {
        // Get all folders in the specified path
        $folders = Storage::directories($path);

        // Filter folders with a specific prefix
        $foldersToDelete = array_filter($folders, function ($folder) use ($prefix) {
            return Str::startsWith(basename($folder), $prefix);
        });

        // Delete each folder
        foreach ($foldersToDelete as $folder) {
            Storage::deleteDirectory($folder);
        }

        // Storage::deleteDirectory($folderPath);
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
        }
    }
}
