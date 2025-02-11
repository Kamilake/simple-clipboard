<?php
// upload_image.php
$maxFileSize = 100 * 1024 * 1024; // 100MB

if ($_FILES['image']['error'] === UPLOAD_ERR_OK || isset($_FILES['file'])) {
    $file = isset($_FILES['image']) ? $_FILES['image'] : $_FILES['file'];
    
    if ($file['size'] > $maxFileSize) {
        http_response_code(400);
        echo json_encode(['error' => '파일 크기가 100MB를 초과합니다']);
        exit;
    }

    $uploadFile = 'file.bin';
    $filenameTxt = 'filename.txt';

    // 기존 파일명이 있으면 filename.txt에 저장
    if (file_exists($uploadFile)) {
        $currentFilename = basename($file['name']);
        file_put_contents($filenameTxt, $currentFilename);
    }

    // 파일 업로드
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        echo json_encode(['url' => $uploadFile]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => '파일 업로드 실패']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => '잘못된 요청']);
}
?>