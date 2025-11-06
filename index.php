<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PHP图床上传工具</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .upload-area {
            border: 2px dashed #ccc;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
            transition: border-color 0.3s;
        }
        .upload-area:hover {
            border-color: #4285f4;
        }
        #fileInput {
            display: none;
        }
        .btn {
            background-color: #4285f4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #3367d6;
        }
        #preview {
            max-width: 100%;
            margin: 20px 0;
            display: none;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .url-box {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>图床图片上传工具</h1>
        
        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
            <p>点击选择图片或拖放至此处</p>
            <input type="file" id="fileInput" accept="image/*">
        </div>
        
        <div style="text-align: center;">
            <button class="btn" onclick="uploadImage()">上传图片</button>
        </div>
        
        <img id="preview" alt="图片预览">
        
        <div id="result" class="result"></div>
    </div>

    <script>
        // 预览选择的图片
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // 拖放功能
        const uploadArea = document.querySelector('.upload-area');
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#4285f4';
        });
        uploadArea.addEventListener('dragleave', function() {
            uploadArea.style.borderColor = '#ccc';
        });
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#ccc';
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                document.getElementById('fileInput').files = e.dataTransfer.files;
                // 触发预览
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // 上传图片（通过PHP代理）
        function uploadImage() {
            const fileInput = document.getElementById('fileInput');
            const file = fileInput.files[0];
            const resultDiv = document.getElementById('result');

            if (!file) {
                showResult('请先选择图片文件！', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('image', file); // 传给PHP的字段名

            fetch('upload_proxy.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`服务器错误：${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.url) {
                    showResult(`上传成功！<br>图片URL：<a href="${data.url}" target="_blank">点击查看</a>`, 'success');
                    // 显示可复制的URL
                    document.querySelector('.result').innerHTML += `<div class="url-box">${data.url}</div>`;
                } else {
                    showResult(`上传失败：${data.message || '未知错误'}`, 'error');
                }
            })
            .catch(error => {
                showResult(`请求错误：${error.message}`, 'error');
            });
        }

        // 显示结果
        function showResult(text, type) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = text;
            resultDiv.className = `result ${type}`;
            resultDiv.style.display = 'block';
        }
    </script>
</body>
</html>
