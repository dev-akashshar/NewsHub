<?php
if (!is_dir('public/icons')) {
    mkdir('public/icons', 0755, true);
}
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];
foreach ($sizes as $s) {
    if (file_exists("public/icons/icon-{$s}.png")) continue;
    $im = imagecreatetruecolor($s, $s);
    $bg = imagecolorallocate($im, 15, 23, 42);
    imagefill($im, 0, 0, $bg);
    $text = imagecolorallocate($im, 255, 255, 255);
    $fontSize = max(2, intval($s / 20));
    imagestring($im, $fontSize, intval($s/2-($fontSize*4)), intval($s/2-($fontSize/2)), 'NH', $text);
    imagepng($im, "public/icons/icon-{$s}.png");
    imagedestroy($im);
}
echo "Icons generated.";
