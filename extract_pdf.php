<?php
$pdf = file_get_contents('Form Asesmen Klien.pdf');
// Extract readable text from PDF
preg_match_all('/BT(.+?)ET/s', $pdf, $matches);
$text = '';
foreach ($matches[1] as $block) {
    preg_match_all('/\(([^)]+)\)/', $block, $strings);
    foreach ($strings[1] as $s) {
        $text .= $s . ' ';
    }
}
echo $text;
