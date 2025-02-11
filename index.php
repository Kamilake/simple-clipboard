<?php
// 사용자 에이전트 가져오기
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// 현재 도메인 가져오기
$domain = $_SERVER['HTTP_HOST'];

// curl 요청 처리
if (strpos($user_agent, 'curl') !== false && isset($_FILES['file'])) {
    $uploadFile = 'file.bin';
    $filenameTxt = 'filename.txt';

    // 파일 업로드
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        $currentFilename = basename($_FILES['file']['name']);
        file_put_contents($filenameTxt, $currentFilename);
        echo "File successfully uploaded.\n";
        echo "파일이 성공적으로 업로드되었습니다.\n";
        exit();
    } else {
        http_response_code(500);
        echo "File upload failed.\n";
        echo "파일 업로드 실패.\n";
        exit();
    }
}

// 사용자 에이전트가 curl인지 확인
if (strpos($user_agent, 'curl') !== false) {
  $httpdata = fopen("php://input", "r");
  if (file_get_contents("php://input") != "") {
    $fp = fopen("text.txt", "w");
    while ($data = fread($httpdata, 1024)) fwrite($fp, $data);
    fclose($fp);
    echo "Data successfully received and stored.\n";
    echo "데이터가 성공적으로 저장되었습니다.\n";
    echo "===============데이터===============\n";
    echo file_get_contents("text.txt");
    echo "\n====================================\n";
    exit();
  } else {
    fclose($httpdata);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="data.txt"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize('text.txt'));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    readfile('text.txt');
    exit();
  }
}

// wget
if (strpos($user_agent, 'Wget') !== false) {
  header('Location: /save.php?download=true');
  exit();
}

// Invoke-WebRequest
if (strpos($user_agent, 'WindowsPowerShell') !== false) {
  header('Location: /save.php?download=true');
  exit();
}

// 파일 정보 가져오기
$uploadedFile = 'file.bin';
$filenameTxt = 'filename.txt';
$fileInfo = '';


function formatBytes($size, $precision = 2) {
  if ($size === 0) return '0 바이트';
  $base = log($size) / log(1024);
  $suffixes = array('바이트', 'KB', 'MB', 'GB', 'TB');
  return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

if (file_exists($uploadedFile) && file_exists($filenameTxt)) {
    $fileSize = filesize($uploadedFile);
    $fileName = file_get_contents($filenameTxt);
    $fileInfo = $fileName . ' (' . formatBytes($fileSize) . ')';
    $fileInfo .= ' 다운로드';
} else {
    $fileInfo = '업로드된 파일이 없음';
}

?>
<!DOCTYPE html>
<html>
<?php
$file = fopen("text.txt", "r");
$text = (filesize("text.txt") == 0) ? "" : fread($file, filesize("text.txt"));
fclose($file);
?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10">
  <title>Simple Clipboard</title>
  <!-- jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <!-- Clipboard -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
  <style>
    #dropZone {
      border: 2px dashed #007bff;
      border-radius: 5px;
      padding: 20px;
      text-align: center;
      color: #007bff;
      margin-top: 20px;
    }
    #dropZone.dragover {
      background-color: #e9ecef;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <br>
        <h1 style="text-align: center;font-weight: lighter;">Simple Clipboard</h1><br>
        <div class="mb-3">
          <div class="input-group-append">
            <!-- copy to clipboard -->
            <button class="btn btn-outline-secondary" type="button" data-clipboard-target="#maindata" onclick="showToast('클립보드에 복사되었습니다!')">클립보드에 복사</button>
            <!-- save to server -->
            <button class="btn btn-outline-secondary" type="button" onclick="saveToServer()">서버로 저장</button>
            <!-- refresh button -->
            <button class="btn btn-outline-secondary" type="button" onclick="location_reload_with_cachebust()">서버에서 불러오기</button>
            <!-- clear text button -->
            <button class="btn btn-outline-secondary" type="button" onclick="$('#maindata').val('');">모두 삭제</button>
            <!-- save to file button -->
            <input type="text" id="filename" class="form-control" value="data" style="width: 100px; display: inline-block;">
            <button class="btn btn-outline-secondary" type="button" onclick="saveToFile()">텍스트 다운로드</button>
            <input type="file" id="binaryFile" class="form-control" style="display: none;">
            <button class="btn btn-outline-secondary" type="button" onclick="$('#binaryFile').click()">파일 업로드</button>
            <button class="btn btn-outline-secondary" type="button" onclick="binaryDownload()">파일 다운로드</button>
            <button class="btn btn-outline-secondary" type="button" onclick="binaryOpen()">파일 열기</button>
            <hr>
            <!-- Drag and Drop Zone -->
            <div id="dropZone">
            <a href="save.php?download=true"><?php echo $fileInfo; ?></a>
              <a href="remove.php">삭제</a><br>
              여기에 파일을 드래그 앤 드롭하세요
            </div>
            <hr>
            <!-- Detailed progress information -->
            <div id="uploadDetails" style="display: none;">
              청크: <span id="chunkUploadedSize">0</span>/<span id="chunkTotalSize">0</span>, <span id="chunkTimeRemaining">0</span> 남음, <span id="currentChunk">0</span> / <span id="totalChunk">0</span>
              <progress id="chunkUploadProgress" value="0" max="100" style="width: 100%;"></progress>
              <hr>
              전체: <span id="uploadedSize">0</span>/<span id="totalSize">0</span>, <span id="timeRemaining">0</span> 남음, <span id="speed">0</span>
              <progress id="uploadProgress" value="0" max="100" style="width: 100%;"></progress>
            </div>
            <textarea class="form-control" id="maindata" value="https://www.google.com" rows="10"><?php echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
        </div>
      </div>
      <div class="position-fixed top-0 start-50 translate-middle-x" style="z-index: 11">
        <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header">
            <strong class="me-auto">Bootstrap</strong>
            <small>11 mins ago</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            Hello, world! This is a toast message.
          </div>
        </div>
      </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11"><small>
    <?php
        $domain = $_SERVER['HTTP_HOST'];
        ?>
        텍스트 업로드: curl <?= $domain; ?> -d "카미"<br>
        텍스트 다운로드: curl <?= $domain; ?><br>
        <hr>
        파일 업로드:<br>
        curl -F 'file=@kami.png' <?= $domain; ?><br>
        파일 다운로드:<br>
        wget <?= $domain; ?> --content-disposition<br>
      </small>
    </div>
    <script>
      var clipboard = new ClipboardJS('.btn');
      function saveToServer() {
        var text = $("#maindata").val();
        $.ajax({
          url: "save.php",
          type: "POST",
          data: { text: text },
          success: saveToServer_success
        });
      }
      function saveToServer_success(data) {
        console.log(data);
        $('.toast-body').text(data);
        $('.toast').toast('show');
      }
      function showToast(data) {
        console.log(data);
        $('.toast-body').text(data);
        $('.toast').toast('show');
      }
      function saveToFile() {
        var text = $("#maindata").val();
        var blob = new Blob([text], { type: "text/plain;charset=utf-8" });
        var fileName = $("#filename").val();
        saveAs(blob, fileName);
      }
      function saveAs(blob, fileName) {
        var link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.download = fileName;
        link.click();
      }
      function formatTime(seconds) {
        var minutes = Math.floor(seconds / 60);
        seconds = seconds % 60;
        return minutes + "분 " + Math.round(seconds) + "초";
      }
      function formatBytes(size, precision = 2) {
        if (size === 0) return '0 바이트';
        let base = Math.log(size) / Math.log(1024);
        let suffixes = ['바이트', 'KB', 'MB', 'GB', 'TB'];
        return (Math.pow(1024, base - Math.floor(base)).toFixed(precision) + suffixes[Math.floor(base)]);
      }
      function location_reload_with_cachebust() {
        var cacheBuster = new Date().getTime();
        var url = new URL(window.location.href);
        url.searchParams.set("cacheBuster", cacheBuster);
        window.location.href = url.toString();
      }
      function ensureCloudflareCacheBypass() {
        var url = new URL(window.location.href);
        if (!url.searchParams.has("__cf_chl_jschl_tk__")) {
          var randomToken = Math.random().toString(36).substring(2);

          // Add the cache bypass parameter to the URL
          url.searchParams.set("__cf_chl_jschl_tk__", randomToken);
          window.history.replaceState({}, document.title, url.toString());
        }
      }
      // Ctrl + S
      $(document).ready(function() {
        $(document).keydown(function(e) {
          if ((e.ctrlKey || e.metaKey) && e.which == 83) {
            e.preventDefault();
            saveToServer();
            return false;
          }
        });
        // Paste event listener for image upload
        $("#maindata").on("paste", function(event) {
          var items = (event.clipboardData || event.originalEvent.clipboardData).items;
          for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf("image") !== -1) {
              showToast("이미지 업로드 중...");
              var file = items[i].getAsFile();
              uploadImage(file);
            }
          }
        });
      });
      function uploadImage(file) {
        var formData = new FormData();
        formData.append("image", file);
        $.ajax({
          url: "upload_image.php",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            showToast("이미지가 성공적으로 업로드되었습니다.");
          },
          error: function() {
            showToast("이미지 업로드 실패.");
          }
        });
      }
      var uniqueIdentifier = "kamikami";
      var CHUNK_SIZE = 1024 * 1024 * 30; // Size of chunks (10MB)
      var totalChunks = 0;
      $("#binaryFile").on("change", async function(event) {
        var file = event.target.files[0];
        var start = 0;
        var end = CHUNK_SIZE;
        var chunkIndex = 0;
        totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        while (start < file.size) {
          var chunk = file.slice(start, end);
          await uploadChunk(chunk, chunkIndex, totalChunks, file.size, file.name, uniqueIdentifier);
          start = end;
          end = start + CHUNK_SIZE;
          chunkIndex++;
        }
      });
      function uploadChunk(chunk, chunkIndex, totalChunks, totalSize, fileName, uniqueIdentifier) {
        return new Promise((resolve, reject) => {
          var formData = new FormData();
          formData.append("file", chunk);
          formData.append("chunkIndex", chunkIndex);
          formData.append("identifier", uniqueIdentifier);
          $.ajax({
            url: "save.php",
            type: "POST",
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            enctype: 'multipart/form-data',
            xhr: function() {
              var xhr = new window.XMLHttpRequest();
              xhr.upload.addEventListener("progress", function(event) {
                if (event.lengthComputable) {
                  var currentTime = new Date().getTime();
                  var elapsedTime = (currentTime - startTime) / 1000;
                  var speed = event.loaded / elapsedTime;
                  var chunkTimeRemaining = (event.total - event.loaded) / speed;
                  var chunkPercentComplete = (event.loaded / event.total) * 100;
                  var formattedChunkUploadedSize = formatBytes(event.loaded, 2);
                  var formattedChunkTotalSize = formatBytes(event.total, 2);
                  var formattedChunkTimeRemaining = formatTime(chunkTimeRemaining);
                  $("#chunkUploadProgress").val(chunkPercentComplete);
                  $("#chunkUploadedSize").text(formattedChunkUploadedSize);
                  $("#chunkTotalSize").text(formattedChunkTotalSize);
                  $("#chunkTimeRemaining").text(formattedChunkTimeRemaining);
                  $("#currentChunk").text(chunkIndex + 1);
                  $("#totalChunk").text(totalChunks);
                  var formattedSpeed = formatBytes(speed, 2) + "/s";
                  var uploadedSize = event.loaded + (chunkIndex * CHUNK_SIZE);
                  var timeRemaining = (totalSize - uploadedSize) / speed;
                  if (timeRemaining < 0) timeRemaining = 0;
                  var formattedUploadedSize = formatBytes(uploadedSize, 2);
                  var formattedTotalSize = formatBytes(totalSize, 2);
                  var formattedTimeRemaining = formatTime(timeRemaining);
                  var percentComplete = (uploadedSize / totalSize) * 100;
                  $("#uploadedSize").text(formattedUploadedSize);
                  $("#totalSize").text(formattedTotalSize);
                  $("#timeRemaining").text(formattedTimeRemaining);
                  $("#speed").text(formattedSpeed);
                  $("#uploadProgress").val(percentComplete);
                }
              }, false);
              return xhr;
            },
            beforeSend: function() {
              startTime = new Date().getTime();
              $("#uploadProgress").show();
              $("#uploadDetails").show();
            },
            success: function(data) {
              //toast
              showToast(data + " " + chunkIndex + "/" + (totalChunks - 1));
              if (chunkIndex >= (totalChunks - 1)) {
                showToast("청크 조립 중...");
                $.ajax({
                  url: "save.php",
                  type: "POST",
                  data: {
                    completed: true,
                    identifier: uniqueIdentifier,
                    fileName: fileName
                  },
                  success: function(data) {
                    showToast(data);
                  },
                  error: function() {
                    showToast("청크 조립 실패.");
                  },
                });
              }
              resolve();
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.log("에러 메시지:", errorThrown);
              console.log("상태 코드:", jqXHR.status);
              console.log("에러 내용:", jqXHR.responseText);
              showToast("청크 업로드 실패, 오류: " + errorThrown + " " + jqXHR.status + " " + jqXHR.responseText + "<br>504 게이트웨이 타임아웃은 파일이 너무 커서 발생할 수 있지만 파일은 대체로 정상적으로 업로드됩니다.");

              reject(); // On error, reject the Promise
            },
          });
        });
      }
      function binaryDownload() {
        // save.php로 요청을 보내고 파일을 받아 다운로드합니다.
        window.location.href = 'save.php?download=true';
      }
      function binaryOpen() {
        // save.php로 요청을 보내고 파일을 받아 엽니다.
        window.open('save.php?download=false', '_blank');
      }
      ensureCloudflareCacheBypass();
      // Drag and Drop
      var dropZone = document.getElementById('dropZone');
      dropZone.addEventListener('dragover', function(event) {
        event.preventDefault();
        dropZone.classList.add('dragover');
      });
      dropZone.addEventListener('dragleave', function(event) {
        event.preventDefault();
        dropZone.classList.remove('dragover');
      });
      dropZone.addEventListener('drop', function(event) {
        event.preventDefault();
        dropZone.classList.remove('dragover');
        var files = event.dataTransfer.files;
        if (files.length > 0) {
          $("#binaryFile")[0].files = files;
          $("#binaryFile").trigger('change');
        }
      });
    </script>
</body>
</html>