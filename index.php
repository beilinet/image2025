<?php
/**
 * 超星图床跨域上传代理PHP脚本
 * 用于解决浏览器直接上传的跨域问题
 */
$targetUrl = 'https://zz.chaoxing.com/author/api/upload';

// 检查是否有文件上传
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => '未找到文件上传字段']);
    exit;
}

$file = $_FILES['file'];

// 检查文件上传错误
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => '文件上传失败，错误码: ' . $file['error']]);
    exit;
}

// 创建临时文件
$tmpPath = $file['tmp_name'];
$fileName = $file['name'];
$fileType = $file['type'];

// 准备POST数据
$postData = new CURLFile($tmpPath, $fileType, $fileName);
$postFields = ['file' => $postData];

// 初始化CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// 设置超时时间
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// 执行请求
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// 检查CURL错误
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => '代理请求失败: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// 设置响应头并返回结果
http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
?>
