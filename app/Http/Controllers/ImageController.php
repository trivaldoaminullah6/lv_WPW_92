<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function index()
    {
        $uploadsDir = public_path('uploads');
        $images = [];
        if (File::exists($uploadsDir)) {
            $files = File::files($uploadsDir);
            // Urutkan file berdasarkan waktu modifikasi terbaru
            usort($files, function ($a, $b) {
                return $b->getMTime() <=> $a->getMTime();
            });
            foreach ($files as $file) {
                $images[] = $file->getFilename();
            }
        }

        $activeDriver = 'Native GD (Fallback)';
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            if (method_exists(\Intervention\Image\ImageManager::class, 'decode')) {
                $activeDriver = 'Intervention Image v4';
            } elseif (method_exists(\Intervention\Image\ImageManager::class, 'read')) {
                $activeDriver = 'Intervention Image v3';
            } elseif (method_exists(\Intervention\Image\ImageManager::class, 'make')) {
                $activeDriver = 'Intervention Image v2';
            }
        } elseif (class_exists(\Intervention\Image\Facades\Image::class) || class_exists(\Intervention\Image\ImageManagerStatic::class)) {
            $activeDriver = 'Intervention Image v2';
        }

        return view('image.index', compact('images', 'activeDriver'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $file = $request->file('photo');
        $uploadsDir = public_path('uploads');
        if (!File::isDirectory($uploadsDir)) {
            File::makeDirectory($uploadsDir, 0755, true, true);
        }

        $baseName = time();
        $ext = $file->getClientOriginalExtension();
        $originalFilename = "{$baseName}_original.{$ext}";
        $resizedFilename = "{$baseName}_resized.{$ext}";
        $croppedFilename = "{$baseName}_cropped.{$ext}";
        $rotatedFilename = "{$baseName}_rotated.{$ext}";
        $watermarkedFilename = "{$baseName}_watermark.{$ext}";

        // Simpan file asli
        $file->move($uploadsDir, $originalFilename);
        $originalPath = $uploadsDir . DIRECTORY_SEPARATOR . $originalFilename;

        // Siapkan watermark default jika belum ada
        $watermarkPath = public_path('assets' . DIRECTORY_SEPARATOR . 'watermark.png');
        if (!File::exists($watermarkPath)) {
            $this->generateDefaultWatermark($watermarkPath);
        }

        $driverUsed = '';

        try {
            // Prioritas 1: Intervention Image v4 (Metode decode())
            if (class_exists(\Intervention\Image\ImageManager::class) && method_exists(\Intervention\Image\ImageManager::class, 'decode')) {
                $driverUsed = 'Intervention Image v4';
                $driver = class_exists(\Intervention\Image\Drivers\Gd\Driver::class)
                    ? new \Intervention\Image\Drivers\Gd\Driver()
                    : new \Intervention\Image\Drivers\Imagick\Driver();
                $manager = new \Intervention\Image\ImageManager($driver);

                // 1. Resize (300 x 300)
                $imgResize = $manager->decode($originalPath);
                $imgResize->resize(300, 300);
                $imgResize->save($uploadsDir . DIRECTORY_SEPARATOR . $resizedFilename);

                // 2. Crop (200 x 200)
                $imgCrop = $manager->decode($originalPath);
                $imgCrop->crop(200, 200);
                $imgCrop->save($uploadsDir . DIRECTORY_SEPARATOR . $croppedFilename);

                // 3. Rotate (45 derajat)
                $imgRotate = $manager->decode($originalPath);
                $imgRotate->rotate(45);
                $imgRotate->save($uploadsDir . DIRECTORY_SEPARATOR . $rotatedFilename);

                // 4. Watermark
                $imgWm = $manager->decode($originalPath);
                $imgWm->insert($watermarkPath, 15, 15, 'bottom-right');
                $imgWm->save($uploadsDir . DIRECTORY_SEPARATOR . $watermarkedFilename);

            // Prioritas 2: Intervention Image v3 (Metode read())
            } elseif (class_exists(\Intervention\Image\ImageManager::class) && method_exists(\Intervention\Image\ImageManager::class, 'read')) {
                $driverUsed = 'Intervention Image v3';
                $driver = class_exists(\Intervention\Image\Drivers\Gd\Driver::class)
                    ? new \Intervention\Image\Drivers\Gd\Driver()
                    : new \Intervention\Image\Drivers\Imagick\Driver();
                $manager = new \Intervention\Image\ImageManager($driver);

                // 1. Resize
                $imgResize = $manager->read($originalPath);
                $imgResize->resize(300, 300);
                $imgResize->save($uploadsDir . DIRECTORY_SEPARATOR . $resizedFilename);

                // 2. Crop
                $imgCrop = $manager->read($originalPath);
                $imgCrop->crop(200, 200);
                $imgCrop->save($uploadsDir . DIRECTORY_SEPARATOR . $croppedFilename);

                // 3. Rotate
                $imgRotate = $manager->read($originalPath);
                $imgRotate->rotate(45);
                $imgRotate->save($uploadsDir . DIRECTORY_SEPARATOR . $rotatedFilename);

                // 4. Watermark
                $imgWm = $manager->read($originalPath);
                $imgWm->place($watermarkPath, 'bottom-right', 15, 15);
                $imgWm->save($uploadsDir . DIRECTORY_SEPARATOR . $watermarkedFilename);

            // Prioritas 3: Intervention Image v2 (Metode make())
            } elseif (class_exists(\Intervention\Image\Facades\Image::class) || class_exists(\Intervention\Image\ImageManagerStatic::class) || (class_exists(\Intervention\Image\ImageManager::class) && method_exists(\Intervention\Image\ImageManager::class, 'make'))) {
                $driverUsed = 'Intervention Image v2';
                $makeImg = function ($path) {
                    if (class_exists(\Intervention\Image\Facades\Image::class)) {
                        return \Intervention\Image\Facades\Image::make($path);
                    }
                    if (class_exists(\Intervention\Image\ImageManagerStatic::class)) {
                        return \Intervention\Image\ImageManagerStatic::make($path);
                    }
                    $mgr = new \Intervention\Image\ImageManager(['driver' => 'gd']);
                    return $mgr->make($path);
                };

                // 1. Resize
                $makeImg($originalPath)->resize(300, 300)->save($uploadsDir . DIRECTORY_SEPARATOR . $resizedFilename);

                // 2. Crop
                $makeImg($originalPath)->crop(200, 200)->save($uploadsDir . DIRECTORY_SEPARATOR . $croppedFilename);

                // 3. Rotate
                $makeImg($originalPath)->rotate(45)->save($uploadsDir . DIRECTORY_SEPARATOR . $rotatedFilename);

                // 4. Watermark
                $makeImg($originalPath)->insert($watermarkPath, 'bottom-right', 15, 15)->save($uploadsDir . DIRECTORY_SEPARATOR . $watermarkedFilename);

            // Prioritas 4: Fallback Native PHP GD
            } else {
                $driverUsed = 'Native PHP GD (Fallback)';
                $this->manipulateWithGd($originalPath, $uploadsDir, $baseName, $ext, $watermarkPath);
            }
        } catch (\Throwable $e) {
            // Fallback aman ke Native GD jika terjadi kendala pada driver Intervention
            $driverUsed = 'Native PHP GD (Fallback)';
            $this->manipulateWithGd($originalPath, $uploadsDir, $baseName, $ext, $watermarkPath);
        }

        return back()->with('success', "Gambar berhasil diunggah dan dimanipulasi dengan {$driverUsed}.")
            ->with('latest', [
                'original' => $originalFilename,
                'resized' => $resizedFilename,
                'cropped' => $croppedFilename,
                'rotated' => $rotatedFilename,
                'watermark' => $watermarkedFilename,
            ]);
    }

    private function generateDefaultWatermark($path)
    {
        $dir = dirname($path);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $im = imagecreatetruecolor(180, 50);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);

        // Background box semi transparan
        $boxColor = imagecolorallocatealpha($im, 0, 0, 0, 80);
        imagefilledrectangle($im, 0, 0, 180, 50, $boxColor);

        // Text watermark
        $textColor = imagecolorallocatealpha($im, 255, 255, 255, 20);
        imagestring($im, 5, 20, 16, 'POLIJE - WSIWF', $textColor);

        imagepng($im, $path);
        imagedestroy($im);
    }

    private function manipulateWithGd($originalPath, $uploadsDir, $baseName, $ext, $watermarkPath)
    {
        $info = getimagesize($originalPath);
        if (!$info) return;

        $mime = $info['mime'];
        $src = null;
        if ($mime == 'image/jpeg') {
            $src = imagecreatefromjpeg($originalPath);
        } elseif ($mime == 'image/png') {
            $src = imagecreatefrompng($originalPath);
        } elseif ($mime == 'image/webp') {
            $src = imagecreatefromwebp($originalPath);
        }
        if (!$src) return;

        $origW = imagesx($src);
        $origH = imagesy($src);

        // 1. Resize (300 x 300)
        $res = imagecreatetruecolor(300, 300);
        imagecopyresampled($res, $src, 0, 0, 0, 0, 300, 300, $origW, $origH);
        $this->saveGdImage($res, $uploadsDir . DIRECTORY_SEPARATOR . "{$baseName}_resized.{$ext}", $ext);
        imagedestroy($res);

        // 2. Crop (200 x 200 tengah)
        $cropW = min(200, $origW);
        $cropH = min(200, $origH);
        $cropX = max(0, intval(($origW - $cropW) / 2));
        $cropY = max(0, intval(($origH - $cropH) / 2));
        $crop = imagecreatetruecolor($cropW, $cropH);
        imagecopyresampled($crop, $src, 0, 0, $cropX, $cropY, $cropW, $cropH, $cropW, $cropH);
        $this->saveGdImage($crop, $uploadsDir . DIRECTORY_SEPARATOR . "{$baseName}_cropped.{$ext}", $ext);
        imagedestroy($crop);

        // 3. Rotate (45 derajat)
        $transparent = imagecolorallocatealpha($src, 0, 0, 0, 127);
        $rot = imagerotate($src, 45, $transparent);
        if ($rot) {
            imagesavealpha($rot, true);
            $this->saveGdImage($rot, $uploadsDir . DIRECTORY_SEPARATOR . "{$baseName}_rotated.{$ext}", $ext);
            imagedestroy($rot);
        }

        // 4. Watermark
        $wmImg = imagecreatetruecolor($origW, $origH);
        imagecopy($wmImg, $src, 0, 0, 0, 0, $origW, $origH);
        if (file_exists($watermarkPath)) {
            $wm = imagecreatefrompng($watermarkPath);
            if ($wm) {
                $wmW = imagesx($wm);
                $wmH = imagesy($wm);
                $dstX = max(10, $origW - $wmW - 10);
                $dstY = max(10, $origH - $wmH - 10);
                imagecopy($wmImg, $wm, $dstX, $dstY, 0, 0, $wmW, $wmH);
                imagedestroy($wm);
            }
        }
        $this->saveGdImage($wmImg, $uploadsDir . DIRECTORY_SEPARATOR . "{$baseName}_watermark.{$ext}", $ext);
        imagedestroy($wmImg);

        imagedestroy($src);
    }

    private function saveGdImage($resource, $path, $ext)
    {
        $ext = strtolower($ext);
        if ($ext === 'png') {
            imagesavealpha($resource, true);
            imagepng($resource, $path);
        } elseif ($ext === 'webp') {
            imagewebp($resource, $path, 80);
        } else {
            imagejpeg($resource, $path, 85);
        }
    }
}
