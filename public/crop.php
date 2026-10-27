<?php
$srcPath = __DIR__ . '/images/logo-k.jpg';
$destPath = __DIR__ . '/images/logo-k.png';

$src = imagecreatefromjpeg($srcPath);
$w = imagesx($src);
$h = imagesy($src);

$img = imagecreatetruecolor($w, $h);
$bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $bg);
imagealphablending($img, false);
imagesavealpha($img, true);

for($x = 0; $x < $w; $x++) {
    for($y = 0; $y < $h; $y++) {
        $cx = $w/2;
        $cy = $h/2;
        $r = $w/2;
        // Make the circle slightly smaller to avoid edge artifacts
        if(pow($x - $cx, 2) + pow($y - $cy, 2) <= pow($r - 2, 2)) {
            $color = imagecolorat($src, $x, $y);
            imagesetpixel($img, $x, $y, $color);
        }
    }
}

imagepng($img, $destPath);
imagedestroy($src);
imagedestroy($img);
echo "Image cropped successfully!";
