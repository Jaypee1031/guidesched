<?php
if (!file_exists(__DIR__ . '/assets/images')) {
    mkdir(__DIR__ . '/assets/images', 0777, true);
}

function makeIcon($size, $path) {
    $img = imagecreatetruecolor($size, $size);
    $purple = imagecolorallocate($img, 109, 40, 217);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, $size, $size, $purple);
    
    // Draw rounded aesthetic circle
    $margin = $size * 0.1;
    $inner_size = $size - ($margin * 2);
    imagefilledellipse($img, $size / 2, $size / 2, $inner_size, $inner_size, imagecolorallocate($img, 124, 58, 237));
    
    // Use string
    $text = "GS";
    $fontSize = 5;
    $fontWidth = imagefontwidth($fontSize);
    $fontHeight = imagefontheight($fontSize);
    $x = ($size - (strlen($text) * $fontWidth * 4)) / 2;
    $y = ($size - ($fontHeight * 4)) / 2;
    
    // Scale text
    $scaled = imagecreatetruecolor($fontWidth * strlen($text), $fontHeight);
    $bg = imagecolorallocate($scaled, 124, 58, 237);
    imagefill($scaled, 0, 0, $bg);
    imagestring($scaled, $fontSize, 0, 0, $text, $white);
    imagecopyresampled($img, $scaled, $x, $y, 0, 0, $fontWidth * strlen($text) * 4, $fontHeight * 4, $fontWidth * strlen($text), $fontHeight);
    imagedestroy($scaled);
    
    imagepng($img, $path);
    imagedestroy($img);
}

makeIcon(192, __DIR__ . '/assets/images/icon-192.png');
makeIcon(512, __DIR__ . '/assets/images/icon-512.png');
echo "PWA Icons generated successfully!\n";
?>
