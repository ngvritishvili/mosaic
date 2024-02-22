<?php

namespace App\Http\Controllers;

use App\Enums\Resolution;
use App\Http\Requests\CanvasStoreRequest;
use App\Models\Category;
use App\Models\ImagesLibrary;
use App\Services\ImageService;
use Database\Seeders\CategorySeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Image;

class MosaicController extends Controller
{
    public ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        return $this->imageService->index();
    }

    public function show()
    {
        return $this->imageService->show();
    }
    public function importImg()
    {
        return $this->imageService->importImg();
    }

    public function importMainImg(Request $request)
    {
        return $this->imageService->importMainImg($request);
    }
    public function countPixelColors()
    {
        return $this->imageService->countPixelColors();
    }

    public function testReq(Request $request)
    {
//        dd(Resolution::list()->pluck('id')->toArray());
        dd(Resolution::R4K->value);
        return $this->imageService->cropImage();
    }
}
