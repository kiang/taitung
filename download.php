<?php

$targetPath = __DIR__ . '/doc';
if (!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
}
$fh = fopen(__DIR__ . '/doc.csv', 'r');
$base = array();
$line = fgetcsv($fh, 2048);
while($line = fgetcsv($fh, 2048)) {
  $base[$line[0]] = $line;
}
$fh = fopen(__DIR__ . '/doc.csv', 'w');
fputcsv($fh, array('id', 'file name', 'doc name'));
for ($i = 1; $i <= 3; $i++) {
    $pageCache = file_get_contents('http://www.ttfd.gov.tw/?act=info_personal&page=' . $i);
    $lines = explode('</tr>', $pageCache);
    foreach ($lines AS $line) {
        if (false !== strpos($line, '?act=download&cmd=upload_file&id=')) {
            $cols = explode('</td>', $line);
            $parts = preg_split('/[="]/i', $cols[1]);
            $idFound = false;
            foreach ($parts AS $part) {
                if (false === $idFound && $part === 'upload_file&id') {
                    $idFound = true;
                } elseif (true === $idFound) {
                    $idFound = $part;
                }
            }
            if (substr($idFound, 0, 4) === '2016') {
                $fileName = trim(strip_tags($cols[1]));
                $p = pathinfo($fileName);
                $targetFile = "{$targetPath}/{$idFound}.{$p['extension']}";
                if (!file_exists($targetFile)) {
                    file_put_contents($targetFile, file_get_contents('http://www.ttfd.gov.tw/?act=download&cmd=upload_file&id=' . $idFound));
                }
                if(isset($base[$idFound])) {
                  unset($base[$idFound]);
                }
                fputcsv($fh, array($idFound, $fileName, "{$idFound}.{$p['extension']}"));
            }
        }
    }
}

foreach($base AS $line) {
  fputcsv($fh, $line);
}
