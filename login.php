<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>检查抽查登记管理系统 - 登录</title>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #0a192f;
            position: relative;
            overflow: hidden;
        }

        /* 动态背景 */
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #0a192f 0%, #0c1b2b 100%);
        }

        .grid {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(26, 115, 232, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26, 115, 232, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: grid 20s linear infinite;
        }

        @keyframes grid {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(50px);
            }
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(26, 115, 232, 0.5);
            border-radius: 50%;
            animation: float 6s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            50% {
                transform: translateY(-20px) translateX(10px);
            }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            width: 400px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: container-appear 0.5s ease-out;
        }

        @keyframes container-appear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 500;
            font-size: 28px;
            position: relative;
        }

        .login-container h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background: #1a73e8;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            color: rgba(255, 255, 255, 0.8);
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
            outline: none;
        }

        .form-group input:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        button:hover {
            background: #1557b0;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(1px);
        }

        .error {
            color: #ff4d4f;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: rgba(255, 77, 79, 0.1);
            border-radius: 8px;
            animation: error-appear 0.3s ease-out;
        }

        @keyframes error-appear {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 添加科技感图标 */
        .tech-icon {
            position: absolute;
            color: rgba(26, 115, 232, 0.2);
            font-size: 24px;
        }

        .icon-1 { top: 20px; left: 20px; animation: rotate 10s linear infinite; }
        .icon-2 { top: 20px; right: 20px; animation: rotate 8s linear infinite reverse; }
        .icon-3 { bottom: 20px; left: 20px; animation: rotate 12s linear infinite reverse; }
        .icon-4 { bottom: 20px; right: 20px; animation: rotate 9s linear infinite; }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="grid"></div>
        <div class="particles">
            <?php for($i = 0; $i < 20; $i++): ?>
            <div class="particle" style="
                left: <?php echo rand(0, 100); ?>%;
                top: <?php echo rand(0, 100); ?>%;
                animation-delay: <?php echo $i * 0.3; ?>s;
            "></div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="login-container">
        <span class="tech-icon icon-1 material-icons">memory</span>
        <span class="tech-icon icon-2 material-icons">security</span>
        <span class="tech-icon icon-3 material-icons">hub</span>
        <span class="tech-icon icon-4 material-icons">lan</span>
        
        <h2>检查抽查登记管理系统</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required placeholder="请输入用户名">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required placeholder="请输入密码">
            </div>
            <button type="submit">登 录</button>
        </form>
    </div>
    <div style="text-align: center; padding: 20px; color: rgba(255, 255, 255, 0.6); position: fixed; bottom: 0; left: 0; right: 0; background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(10px);">
        Copyright © 2025 TingZhou_You 原创开发 版权所有 系统升级请微信联系 TingZhou_You
    </div>
</body>
</html>