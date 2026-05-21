<?php

$root = dirname(__DIR__);
$srcPath = $root . '/public/img/logo-bd.png';
$outSize = 256;

if (! is_file($srcPath)) {
    fwrite(STDERR, "Source not found: {$srcPath}\n");
    exit(1);
}

$src = imagecreatefrompng($srcPath);
$w = imagesx($src);
$h = imagesy($src);
$cropSize = min($w, $h);
$srcX = (int) (($w - $cropSize) / 2);
$srcY = (int) (($h - $cropSize) / 2);

$cropped = imagecreatetruecolor($cropSize, $cropSize);
imagealphablending($cropped, false);
imagesavealpha($cropped, true);
imagecopy($cropped, $src, 0, 0, $srcX, $srcY, $cropSize, $cropSize);

$scaled = imagecreatetruecolor($outSize, $outSize);
imagealphablending($scaled, true);
imagesavealpha($scaled, true);
imagecopyresampled($scaled, $cropped, 0, 0, 0, 0, $outSize, $outSize, $cropSize, $cropSize);

$out = imagecreatetruecolor($outSize, $outSize);
imagealphablending($out, false);
imagesavealpha($out, true);
$transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
imagefill($out, 0, 0, $transparent);

$cx = $outSize / 2;
$cy = $outSize / 2;
$r = $outSize / 2;

for ($x = 0; $x < $outSize; $x++) {
    for ($y = 0; $y < $outSize; $y++) {
        $dx = $x - $cx + 0.5;
        $dy = $y - $cy + 0.5;
        if (($dx * $dx + $dy * $dy) <= ($r * $r)) {
            imagesetpixel($out, $x, $y, imagecolorat($scaled, $x, $y));
        }
    }
}

foreach ([
    $root . '/public/img/logo-bd-favicon.png',
    $root . '/public/assets/img/logo-bd-favicon.png',
] as $dest) {
    imagepng($out, $dest);
    echo "Wrote {$dest}\n";
}

imagedestroy($src);
imagedestroy($cropped);
imagedestroy($scaled);
imagedestroy($out);
