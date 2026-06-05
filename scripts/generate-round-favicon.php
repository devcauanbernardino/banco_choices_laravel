<?php

$src = __DIR__.'/../public/img/logo-bd.png';
$outSvg = __DIR__.'/../public/img/favicon-bd-round.svg';
$outPng = __DIR__.'/../public/img/favicon-bd-round.png';
$size = 128;

if (! is_file($src)) {
    fwrite(STDERR, "Source not found: $src\n");
    exit(1);
}

if (! extension_loaded('gd')) {
    fwrite(STDERR, "GD extension required\n");
    exit(1);
}

$source = imagecreatefrompng($src);
if ($source === false) {
    fwrite(STDERR, "Failed to load PNG\n");
    exit(1);
}

$srcW = imagesx($source);
$srcH = imagesy($source);

$canvas = imagecreatetruecolor($size, $size);
imagealphablending($canvas, false);
imagesavealpha($canvas, true);
$transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
imagefill($canvas, 0, 0, $transparent);

$scale = $size / max($srcW, $srcH);
$dstW = (int) round($srcW * $scale);
$dstH = (int) round($srcH * $scale);
$dstX = (int) round(($size - $dstW) / 2);
$dstY = (int) round(($size - $dstH) / 2);

$resized = imagecreatetruecolor($dstW, $dstH);
imagealphablending($resized, false);
imagesavealpha($resized, true);
imagefill($resized, 0, 0, $transparent);
imagecopyresampled($resized, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

$radius = $size / 2;
for ($y = 0; $y < $size; $y++) {
    for ($x = 0; $x < $size; $x++) {
        $dx = $x - $radius + 0.5;
        $dy = $y - $radius + 0.5;
        if (($dx * $dx + $dy * $dy) > ($radius * $radius)) {
            imagesetpixel($canvas, $x, $y, $transparent);
            continue;
        }

        $sx = $x - $dstX;
        $sy = $y - $dstY;
        if ($sx < 0 || $sy < 0 || $sx >= $dstW || $sy >= $dstH) {
            imagesetpixel($canvas, $x, $y, $transparent);
            continue;
        }

        $color = imagecolorat($resized, $sx, $sy);
        imagesetpixel($canvas, $x, $y, $color);
    }
}

imagepng($canvas, $outPng);

$pngData = file_get_contents($outPng);
if ($pngData === false) {
    fwrite(STDERR, "Failed to read generated PNG\n");
    exit(1);
}

$b64 = base64_encode($pngData);
$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}" role="img" aria-hidden="true">
  <image href="data:image/png;base64,{$b64}" width="{$size}" height="{$size}" preserveAspectRatio="xMidYMid meet"/>
</svg>
SVG;

file_put_contents($outSvg, $svg);

imagedestroy($source);
imagedestroy($resized);
imagedestroy($canvas);

echo "OK: $outPng\n";
echo "OK: $outSvg\n";
