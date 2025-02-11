<?php
function formatBytes($size, $precision = 2)
{
  if ($size == 0) {
    return '0 바이트';
  }
  $base = log($size, 1024);
  $suffixes = array('바이트', 'KB', 'MB', 'GB', 'TB');
  return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

// 텍스트 데이터 저장
if (isset($_POST['text'])) {
  $text = $_POST['text'];
  $file = fopen("text.txt", "w");
  fwrite($file, $text);
  fclose($file);
  $filesize = filesize("text.txt");
  echo "[200] 성공!, " . formatBytes($filesize) . " 저장됨";
}

// is post or get
// echo $_SERVER['REQUEST_METHOD'];
// echo "\n";
// echo isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : "no file";
// echo "\n";
// echo isset($_POST['identifier']) ? $_POST['identifier'] : "no identifier";
// echo "\n";
// echo isset($_POST['chunkIndex']) ? $_POST['chunkIndex'] : "no chunkIndex";
// 청크 파일 파일 업로드
if (isset($_FILES['file'])) {
  $identifier = $_POST['identifier'];
  $chunkIndex = $_POST['chunkIndex'];

  // 만약 chunkIndex가 0이면 업로드 폴더 비우기
  if ($chunkIndex == 0) {
    $files = glob("./uploads/*");
    foreach ($files as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }
  }

  $filePath = "./uploads/" . $identifier . "_" . $chunkIndex;
  $file = fopen($filePath, 'w');
  fwrite($file, file_get_contents($_FILES['file']['tmp_name']));
  fclose($file);

  // echo "[200] 업로드 성공!, " . formatBytes($_FILES['file']['size']) . " 저장됨";
  echo "청크 " . $chunkIndex . " 업로드 성공!, " . formatBytes($_FILES['file']['size']) . " 저장됨";
  exit;
}

$finalFilePath = "./file.bin";

// 파일 조립 완료
if (isset($_POST['completed']) && $_POST['completed']) {
  $identifier = $_POST['identifier'];
  $fileName = $_POST['fileName'];
  
  $chunks = glob("./uploads/" . $identifier . "_*");

  usort($chunks, function ($a, $b) {
    return intval(explode('_', $a)[1]) > intval(explode('_', $b)[1]);
  });

  // 기존 파일 삭제
  if (file_exists($finalFilePath)) {
    unlink($finalFilePath);
  }

  foreach ($chunks as $chunk) {
    // 조립
    $file = fopen($finalFilePath, 'a');
    fwrite($file, file_get_contents($chunk));
    fclose($file);
    unlink($chunk);
  }
  $file = fopen("filename.txt", "w");
  fwrite($file, $fileName);
  fclose($file);

  echo "[200] 조립 완료!, " . formatBytes(filesize($finalFilePath)) . " 저장됨";
}




// 파일 파일 다운로드 또는 보기
if (isset($_GET['download'])) {
  $fileNamePath = "filename.txt";

  if (file_exists($fileNamePath)) {
    $file = fopen($fileNamePath, "r");
    $fileName = fread($file, filesize($fileNamePath));
    fclose($file);
  }

  if (file_exists($finalFilePath)) {
    if ($_GET['download'] === 'false') {
      // 파일 내용을 보여줌
      $mimeType = mime_content_type($finalFilePath);
      header('Content-Type: ' . $mimeType);
      readfile($finalFilePath);
    } else {
      // 파일 다운로드
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($finalFilePath));

      $handle = fopen($finalFilePath, "rb");
      $chunkSize = 1024 * 1024 * 10; // 10MB 청크 사이즈

      // 파일을 청크로 나누어 읽고, 출력합니다.
      while (!feof($handle)) {
        $buffer = fread($handle, $chunkSize);
        echo $buffer;
        ob_flush();
        flush();
      }
      fclose($handle);
    }
    exit;
  } else {
    header('Content-Type: text/html; charset=utf-8');
    echo "[404] 파일을 찾을 수 없습니다!";
  }
}
?>