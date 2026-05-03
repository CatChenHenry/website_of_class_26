<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title>401 - 未登录</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft Yahei", sans-serif;
        }

        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }

        .error-code {
            font-size: 80px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .error-message {
            font-size: 20px;
            color: #333;
            margin-bottom: 30px;
        }

        .error-link {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .error-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-code">401</div>
        <div class="error-message">请先登录</div>
        <div class="error-message">3秒后自动返回登录界面</div>
        <script>
            setTimeout(() => {
                window.location.href = "/user/login";
            }, 3000);
        </script>
    </div>
</body>

</html>