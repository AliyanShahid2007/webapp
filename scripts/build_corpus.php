<?php
// Usage: php scripts/build_corpus.php

$root = realpath(__DIR__ . '/..');
$outFile = $root . '/data/site_corpus.json';

$exts = ['php','html','htm','md','txt'];
$files = [];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($it as $f){
    if (!$f->isFile()) continue;
    $path = $f->getPathname();
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    // skip vendor, node_modules, data, uploads
    if (preg_match('#^(vendor|node_modules|data|uploads|assets|docs|scripts)/#', str_replace('\\','/', $rel))) continue;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), $exts)) continue;
    $files[] = $path;
}

$corpus = [];
echo "Scanned " . count($files) . " candidate files\n";
foreach ($files as $f){
    $raw = file_get_contents($f);
    // remove PHP blocks
    $raw = preg_replace('/<\?php.*?\?>/s', ' ', $raw);
    // remove inline scripts and styles before stripping tags
    $raw = preg_replace('/<script[^>]*>.*?<\/script>/is', ' ', $raw);
    $raw = preg_replace('/<style[^>]*>.*?<\/style>/is', ' ', $raw);
    // strip tags
    $text = html_entity_decode(strip_tags($raw));
    // remove lines that look like code (many non-word characters) to avoid dumping CSS/JS
    $lines = preg_split('/\r?\n/', $text);
    $filtered = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $len = max(1, mb_strlen($line));
        // count non-letter/number/space characters
        preg_match_all('/[^\p{L}\p{N}\s]/u', $line, $m);
        $non = count($m[0]);
        if ($non / $len > 0.25) continue; // likely code or CSS
        // skip very long single-token lines (files paths, base64, etc.)
        if (preg_match('/^\S{200,}$/', $line)) continue;
        $filtered[] = $line;
    }
    $text = preg_replace('/\s+/', ' ', implode(' ', $filtered));
    $text = trim($text);
    // lower threshold so more pages are included for debugging
    if (strlen($text) < 20) continue;
    // print small summary for debugging
    echo "- " . str_replace($root . DIRECTORY_SEPARATOR, '', $f) . " (" . strlen($text) . " chars)\n";
    $corpus[] = ['path' => str_replace($root . DIRECTORY_SEPARATOR, '', $f), 'text' => $text];
}

if (!is_dir(dirname($outFile))) mkdir(dirname($outFile), 0755, true);
file_put_contents($outFile, json_encode($corpus, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Wrote " . count($corpus) . " documents to data/site_corpus.json\n";
