<?php
// 삭제할 파일 경로
$uploadedFile = 'file.bin';
$filenameTxt = 'filename.txt';

// 파일이 존재하면 삭제
if (file_exists($uploadedFile)) {
    unlink($uploadedFile);
}

if (file_exists($filenameTxt)) {
    unlink($filenameTxt);
}

// 루트 페이지로 리디렉션
header('Location: /');
exit();
?>