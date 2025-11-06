<?php
// 允许前端跨域请求
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

// 检查是否有文件上传
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => '未找到有效图片文件'
    ]);
    exit;
}

// 获取上传的文件信息
$file = $_FILES['image'];
$tmpPath = $file['tmp_name']; // 临时文件路径
$fileName = $file['name'];    // 原始文件名
$fileType = $file['type'];    // 文件类型

// 验证文件类型（仅允许图片）
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => '仅支持JPG、PNG、GIF、WEBP格式的图片'
    ]);
    exit;
}

// 读取文件内容
$fileContent = file_get_contents($tmpPath);
if (!$fileContent) {
    echo json_encode([
        'success' => false,
        'message' => '无法读取图片内容'
    ]);
    exit;
}

// 准备转发到超星图床接口
$apiUrl = 'https://zz.chaoxing.com/author/api/upload';

// 构建multipart/form-data格式的请求
$boundary = '----WebKitFormBoundary' . md5(time());
$headers = [
    "Content-Type: multipart/form-data; boundary={$boundary}",
];

// 构建请求体
$body = "--{$boundary}\r\n";
$body .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$fileName}\"\r\n";
$body .= "Content-Type: {$fileType}\r\n\r\n";
$body .= $fileContent . "\r\n";
$body .= "--{$boundary}--\r\n";

// 发送POST请求（使用cURL）
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL证书验证（仅测试用）
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 超时时间30秒

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode([
        'success' => false,
        'message' => "请求图床失败：{$error}"
    ]);
    exit;
}

// 解析图床返回的响应
$responseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => "图床响应格式错误：{$response}"
    ]);
    exit;
}

// 检查是否返回图片URL
if (isset($responseData['data']) && !empty($responseData['data'])) {
    echo json_encode([
        'success' => true,
        'url' => $responseData['data']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "图床返回异常：" . json_encode($responseData)
    ]);
}
?>