<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// 处理保存、更新和删除操作
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'save') {
                $stmt = $pdo->prepare("INSERT INTO inspections (id, check_time, inspector, inspected_unit, inspection_details, remarks) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['custom_id'],
                    $_POST['check_time'],
                    $_POST['inspector'],
                    $_POST['inspected_unit'],
                    $_POST['inspection_details'],
                    $_POST['remarks']
                ]);
                $message = '记录添加成功！';
                $messageType = 'success';
                // 添加重定向，防止表单重复提交
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } elseif ($_POST['action'] == 'update') {
                $stmt = $pdo->prepare("UPDATE inspections SET check_time = ?, inspector = ?, inspected_unit = ?, inspection_details = ?, remarks = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['check_time'],
                    $_POST['inspector'],
                    $_POST['inspected_unit'],
                    $_POST['inspection_details'],
                    $_POST['remarks'],
                    $_POST['id']
                ]);
                $message = '记录更新成功！';
                $messageType = 'success';
                // 添加重定向，防止表单重复提交
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } elseif ($_POST['action'] == 'delete') {
                $stmt = $pdo->prepare("DELETE FROM inspections WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = '记录删除成功！';
                $messageType = 'success';
                // 添加重定向，防止表单重复提交
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
        }
    } catch (PDOException $e) {
        $message = '操作失败：' . $e->getMessage();
        $messageType = 'error';
    }
}

// 获取所有记录
try {
    $query = "SELECT id, DATE_FORMAT(check_time, '%Y-%m-%d %H:%i') as formatted_time, check_time, inspector, inspected_unit, inspection_details, remarks FROM inspections WHERE 1=1";
    $params = [];

    // 检查人筛选
    if (isset($_GET['inspector']) && !empty($_GET['inspector'])) {
        $query .= " AND inspector LIKE ?";
        $params[] = '%' . $_GET['inspector'] . '%';
    }

    // 受检单位筛选
    if (isset($_GET['inspected_unit']) && !empty($_GET['inspected_unit'])) {
        $query .= " AND inspected_unit LIKE ?";
        $params[] = '%' . $_GET['inspected_unit'] . '%';
    }

    // 时间范围筛选
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $query .= " AND check_time >= ?";
        $params[] = $_GET['start_date'] . ' 00:00:00';
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $query .= " AND check_time <= ?";
        $params[] = $_GET['end_date'] . ' 23:59:59';
    }

    $query .= " ORDER BY id DESC LIMIT 50";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = '获取记录失败：' . $e->getMessage();
    $messageType = 'error';
    $records = [];
}

// 获取最大序号
try {
    $maxId = $pdo->query("SELECT MAX(id) as max_id FROM inspections")->fetch(PDO::FETCH_ASSOC)['max_id'];
    $nextId = $maxId ? $maxId + 1 : 1;
} catch (PDOException $e) {
    $nextId = 1;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>检查抽查登记管理系统</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            margin: 0;
            background: #0a192f;
            color: #fff;
            line-height: 1.6;
            position: relative;
            min-height: 100vh;
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
            0% { transform: translateY(0); }
            100% { transform: translateY(50px); }
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
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-20px) translateX(10px); }
        }

        .container {
            max-width: 100%;
            margin: 0;
            padding: 20px;
            position: relative;
            z-index: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 24px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: card-appear 0.5s ease-out;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        @keyframes card-appear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(26, 115, 232, 0) 0%,
                rgba(26, 115, 232, 0.5) 50%,
                rgba(26, 115, 232, 0) 100%
            );
            animation: border-glow 3s infinite;
        }

        @keyframes border-glow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .header h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header h1 .material-icons {
            color: #1a73e8;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .logout {
            background-color: rgba(220, 53, 69, 0.8);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout:hover {
            background-color: rgba(200, 35, 51, 0.9);
            transform: translateY(-1px);
        }

        .table-container {
            flex: 1;
            overflow: auto;
            margin-top: 20px;
            padding: 0 4px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }

        th, td {
            padding: 16px 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            line-height: 1.5;
        }

        th {
            background: rgba(26, 115, 232, 0.95);
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 2;
            overflow: hidden;
            color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        th::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            animation: shine 3s infinite;
            z-index: 1;
        }

        @keyframes shine {
            to {
                left: 100%;
            }
        }

        th:first-child {
            border-top-left-radius: 8px;
        }

        th:last-child {
            border-top-right-radius: 8px;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .inspection-details {
            width: 300px;
        }

        input[type="text"], 
        input[type="datetime-local"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s;
        }

        input[type="text"]:focus, 
        input[type="datetime-local"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
            outline: none;
        }

        .edit-btn {
            background: rgba(26, 115, 232, 0.8);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .edit-btn:hover {
            background: rgba(21, 87, 176, 0.9);
            transform: translateY(-1px);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .material-icons {
            font-size: 18px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .delete-btn {
            background: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .delete-btn:hover {
            background: rgba(200, 35, 51, 0.9);
            transform: translateY(-1px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(12, 27, 43, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: modal-appear 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            width: 90%;
            max-width: 800px;
        }

        .modal-content.small {
            max-width: 400px;
            text-align: center;
        }

        @keyframes modal-appear {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        /* 修复表单行样式 */
        .form-row {
            display: flex;
            gap: 20px;
            width: 100%;
        }

        /* 优化滚动条样式 */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: rgba(26, 115, 232, 0.5);
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: rgba(26, 115, 232, 0.7);
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 120px;
        }

        .confirm-btn {
            background: #1a73e8;
            color: white;
        }

        .confirm-btn:hover {
            background: #1557b0;
            transform: translateY(-1px);
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .cancel-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            animation: message-appear 0.3s ease-out;
        }

        @keyframes message-appear {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background: rgba(40, 167, 69, 0.2);
            color: #98ff98;
            border: 1px solid rgba(40, 167, 69, 0.3);
            display: block;
        }

        .message.error {
            background: rgba(220, 53, 69, 0.2);
            color: #ff8080;
            border: 1px solid rgba(220, 53, 69, 0.3);
            display: block;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        /* 筛选区域样式 */
        .filter-section {
            margin: 16px 0;
            padding: 16px;
            background: rgba(26, 115, 232, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(26, 115, 232, 0.1);
            position: relative;
            overflow: hidden;
        }

        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(26, 115, 232, 0) 0%,
                rgba(26, 115, 232, 0.3) 50%,
                rgba(26, 115, 232, 0) 100%
            );
        }

        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .filter-row {
            display: flex;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            position: relative;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 4px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-weight: 500;
            position: relative;
            padding-left: 20px;
        }

        .filter-group label::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 2px;
            background: #1a73e8;
            border-radius: 1px;
        }

        .filter-input {
            width: 100%;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(26, 115, 232, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            transition: all 0.3s;
        }

        .filter-input:hover {
            border-color: rgba(26, 115, 232, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .filter-input:focus {
            border-color: #1a73e8;
            background: rgba(26, 115, 232, 0.1);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.15);
            outline: none;
        }

        .filter-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .filter-buttons {
            display: flex;
            gap: 12px;
            margin-left: auto;
            position: relative;
        }

        .filter-buttons::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 24px;
            background: rgba(255, 255, 255, 0.1);
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .filter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .filter-btn:hover::before {
            opacity: 1;
        }

        .search-btn {
            background: #1a73e8;
            min-width: 120px;
            justify-content: center;
        }

        .search-btn:hover {
            background: #1557b0;
            transform: translateY(-1px);
        }

        .reset-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 100px;
            justify-content: center;
        }

        .reset-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .filter-btn .material-icons {
            font-size: 18px;
            transition: transform 0.3s;
        }

        .filter-btn:hover .material-icons {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .filter-section {
                padding: 20px;
                margin: 16px 0;
            }

            .filter-row {
                flex-direction: column;
                gap: 16px;
            }
            
            .filter-buttons {
                margin: 8px 0 0 0;
                justify-content: stretch;
                gap: 10px;
            }

            .filter-buttons::before {
                display: none;
            }

            .filter-btn {
                flex: 1;
            }
        }

        /* 主题切换按钮样式 */
        .theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .theme-toggle .material-icons {
            transition: transform 0.3s;
        }

        .theme-toggle:hover .material-icons {
            transform: rotate(180deg);
        }

        /* 浅色主题样式 */
        body.light-theme {
            background: #f5f7fa;
            color: #2c3e50;
        }

        body.light-theme .theme-toggle {
            background: rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: #2c3e50;
        }

        body.light-theme .theme-toggle:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        body.light-theme .background::before {
            background: linear-gradient(45deg, #f5f7fa 0%, #e4e7eb 100%);
        }

        body.light-theme .grid {
            background-image: 
                linear-gradient(rgba(26, 115, 232, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26, 115, 232, 0.05) 1px, transparent 1px);
        }

        body.light-theme .card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        body.light-theme .header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        body.light-theme .header h1 {
            color: #2c3e50;
        }

        body.light-theme th {
            background: rgba(255, 255, 255, 0.95);
            color: #1a73e8;
            border-bottom: 1px solid rgba(26, 115, 232, 0.2);
        }

        body.light-theme td {
            color: #2c3e50;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        body.light-theme tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        body.light-theme .form-control,
        body.light-theme .filter-input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.2);
            color: #2c3e50;
        }

        body.light-theme .form-control:focus,
        body.light-theme .filter-input:focus {
            border-color: #1a73e8;
            background: #fff;
        }

        body.light-theme .form-control::placeholder,
        body.light-theme .filter-input::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }

        body.light-theme .filter-section {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        body.light-theme .filter-group label {
            color: #2c3e50;
        }

        body.light-theme .modal-content {
            background: rgba(255, 255, 255, 0.95);
            color: #2c3e50;
        }

        body.light-theme .modal-content h3 {
            color: #1a73e8;
        }

        body.light-theme .modal-content label {
            color: #2c3e50;
        }

        body.light-theme .modal-content input,
        body.light-theme .modal-content textarea {
            color: #2c3e50;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.2);
        }

        body.light-theme .modal-content input:focus,
        body.light-theme .modal-content textarea:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
        }

        body.light-theme .modal-content input:disabled {
            background: #f5f5f5;
            color: #666;
        }

        body.light-theme .modal-content p {
            color: #2c3e50;
        }

        body.light-theme .message.success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        body.light-theme .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        body.light-theme .modal-content.small h3 {
            color: #2c3e50;
        }

        body.light-theme .modal-content.small p {
            color: #2c3e50;
        }

        body.light-theme .modal-content.small .modal-btn {
            color: #fff;
        }

        body.light-theme div[style*="position: fixed"] {
            color: rgba(0, 0, 0, 0.6) !important;
            background: rgba(255, 255, 255, 0.8) !important;
        }

        body.light-theme .reset-btn {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.2);
        }

        body.light-theme .reset-btn:hover {
            background: rgba(108, 117, 125, 0.2);
            color: #5a6268;
        }

        /* 修改版权信息样式 */
        .copyright {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            z-index: 2;
        }

        body.light-theme .copyright {
            color: #1a73e8 !important;  /* 使用亮眼的蓝色 */
            background: rgba(255, 255, 255, 0.8) !important;  /* 更改背景为半透明白色 */
        }

        /* 优化表格滚动条样式 */
        .table-container::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: rgba(26, 115, 232, 0.5);
            border-radius: 5px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: rgba(26, 115, 232, 0.7);
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        /* 浅色主题下的滚动条样式 */
        body.light-theme .table-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        body.light-theme .table-container::-webkit-scrollbar-thumb {
            background: rgba(26, 115, 232, 0.3);
        }

        body.light-theme .table-container::-webkit-scrollbar-thumb:hover {
            background: rgba(26, 115, 232, 0.5);
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

    <div class="container">
        <div class="card">
            <div class="header">
                <h1>
                    <span class="material-icons"></span>
                    检查抽查登记管理系统 开发者 TingZhou_You V1.0
                </h1>
                <div style="display: flex; gap: 16px;">
                    <button onclick="showNewRecordForm()" class="edit-btn">
                        <span class="material-icons"></span>
                        新增记录
                    </button>
                    <button onclick="toggleTheme()" class="theme-toggle" id="themeToggle">
                        <span class="material-icons"></span>
                        <span id="themeText">深色模式</span>
                    </button>
                    <a href="logout.php" class="logout">
                        <span class="material-icons"></span>
                        退出登录
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- 筛选区域 -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>
                                <span class="material-icons" style="font-size: 16px; margin-right: 4px; vertical-align: middle;"></span>
                                检查人
                            </label>
                            <input type="text" name="inspector" class="filter-input" value="<?php echo isset($_GET['inspector']) ? htmlspecialchars($_GET['inspector']) : ''; ?>" placeholder="输入检查人姓名">
                        </div>
                        <div class="filter-group">
                            <label>
                                <span class="material-icons" style="font-size: 16px; margin-right: 4px; vertical-align: middle;"></span>
                                受检单位
                            </label>
                            <input type="text" name="inspected_unit" class="filter-input" value="<?php echo isset($_GET['inspected_unit']) ? htmlspecialchars($_GET['inspected_unit']) : ''; ?>" placeholder="输入受检单位名称">
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>
                                <span class="material-icons" style="font-size: 16px; margin-right: 4px; vertical-align: middle;"></span>
                                开始日期
                            </label>
                            <input type="date" name="start_date" class="filter-input" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                        </div>
                        <div class="filter-group">
                            <label>
                                <span class="material-icons" style="font-size: 16px; margin-right: 4px; vertical-align: middle;"></span>
                                结束日期
                            </label>
                            <input type="date" name="end_date" class="filter-input" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="filter-btn search-btn">
                                <span class="material-icons"></span>
                                搜索
                            </button>
                            <a href="?" class="filter-btn reset-btn">
                                <span class="material-icons"></span>
                                重置
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 添加表格容器 -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px">序号</th>
                            <th style="width: 180px">检查时间</th>
                            <th style="width: 100px">检查人</th>
                            <th style="width: 150px">受检单位</th>
                            <th class="inspection-details">检查情况</th>
                            <th style="width: 120px">备注</th>
                            <th style="width: 100px">操作</th>
                        </tr>
                    </thead>
                    <tbody id="recordsTable">
                        <?php foreach ($records as $record): ?>
                        <tr id="row_<?php echo $record['id']; ?>">
                            <td><?php echo $record['id']; ?></td>
                            <td data-original-time="<?php echo $record['check_time']; ?>"><?php echo $record['formatted_time']; ?></td>
                            <td><?php echo $record['inspector']; ?></td>
                            <td><?php echo $record['inspected_unit']; ?></td>
                            <td class="inspection-details"><?php echo nl2br(htmlspecialchars($record['inspection_details'])); ?></td>
                            <td><?php echo $record['remarks']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="editRecord(<?php echo $record['id']; ?>)" class="edit-btn">
                                        <span class="material-icons"></span>
                                        编辑
                                    </button>
                                    <button onclick="showDeleteConfirm(<?php echo $record['id']; ?>)" class="delete-btn">
                                        <span class="material-icons">删除</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 新增记录表单 -->
    <div id="newRecordModal" class="modal">
        <div class="modal-content">
            <h3 style="text-align: center; margin-bottom: 24px; color: #1a73e8; font-size: 20px;">新增记录</h3>
            <form id="newRecordForm" method="POST" style="display: grid; gap: 20px;">
                <input type="hidden" name="action" value="save">
                <div class="form-row">
                    <div class="form-group" style="flex: 0.5;">
                        <label>序号：</label>
                        <input type="number" name="custom_id" required class="form-control" value="<?php echo $nextId; ?>" min="1">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>检查时间：</label>
                        <input type="datetime-local" name="check_time" required class="form-control" id="new_check_time">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>检查人：</label>
                        <input type="text" name="inspector" required class="form-control">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>受检单位：</label>
                        <input type="text" name="inspected_unit" required class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>检查情况：</label>
                    <textarea name="inspection_details" rows="6" required class="form-control" style="min-height: 120px;"></textarea>
                </div>
                <div class="form-group">
                    <label>备注：</label>
                    <input type="text" name="remarks" class="form-control">
                </div>
                <div class="modal-buttons" style="margin-top: 30px;">
                    <button type="submit" class="modal-btn confirm-btn">
                        <span class="material-icons"></span>
                        保存
                    </button>
                    <button type="button" onclick="closeNewRecordModal()" class="modal-btn cancel-btn">
                        <span class="material-icons"></span>
                        取消
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 编辑记录表单 -->
    <div id="editRecordModal" class="modal">
        <div class="modal-content">
            <h3 style="text-align: center; margin-bottom: 24px; color: #1a73e8; font-size: 20px;">编辑记录</h3>
            <form id="editRecordForm" method="POST" style="display: grid; gap: 20px;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_record_id">
                <div class="form-row">
                    <div class="form-group" style="flex: 0.5;">
                        <label>序号：</label>
                        <input type="number" disabled class="form-control" id="edit_id">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>检查时间：</label>
                        <input type="datetime-local" name="check_time" required class="form-control" id="edit_check_time">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>检查人：</label>
                        <input type="text" name="inspector" required class="form-control" id="edit_inspector">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>受检单位：</label>
                        <input type="text" name="inspected_unit" required class="form-control" id="edit_inspected_unit">
                    </div>
                </div>
                <div class="form-group">
                    <label>检查情况：</label>
                    <textarea name="inspection_details" rows="6" required class="form-control" style="min-height: 120px;" id="edit_inspection_details"></textarea>
                </div>
                <div class="form-group">
                    <label>备注：</label>
                    <input type="text" name="remarks" class="form-control" id="edit_remarks">
                </div>
                <div class="modal-buttons" style="margin-top: 30px;">
                    <button type="submit" class="modal-btn confirm-btn">
                        <span class="material-icons"></span>
                        保存
                    </button>
                    <button type="button" onclick="closeEditModal()" class="modal-btn cancel-btn">
                        <span class="material-icons"></span>
                        取消
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 删除确认对话框 -->
    <div id="deleteModal" class="modal">
        <div class="modal-content small">
            <h3 style="margin-bottom: 20px;">确认删除</h3>
            <p style="margin-bottom: 30px;">确定要删除这条记录吗？此操作不可撤销。</p>
            <div class="modal-buttons">
                <button onclick="confirmDelete()" class="modal-btn confirm-btn" style="background: #dc3545;">
                    <span class="material-icons"></span>
                    确认删除
                </button>
                <button onclick="closeDeleteModal()" class="modal-btn cancel-btn">
                    <span class="material-icons"></span>
                    取消
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentDeleteId = null;

    function showNewRecordForm() {
        document.getElementById('newRecordModal').style.display = 'block';
    }

    function closeNewRecordModal() {
        document.getElementById('newRecordModal').style.display = 'none';
    }

    function editRecord(id) {
        const row = document.getElementById('row_' + id);
        const cells = row.cells;
        
        // 获取原始内容
        const id_value = cells[0].textContent.trim();
        const checkTime = cells[1].getAttribute('data-original-time') || cells[1].textContent.trim().replace(' ', 'T');
        const inspector = cells[2].textContent.trim();
        const inspectedUnit = cells[3].textContent.trim();
        const inspectionDetails = cells[4].innerHTML.replace(/<br\s*\/?>/g, '\n').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').trim();
        const remarks = cells[5].textContent.trim();
        
        // 填充编辑表单
        document.getElementById('edit_record_id').value = id_value;
        document.getElementById('edit_id').value = id_value;
        document.getElementById('edit_check_time').value = checkTime;
        document.getElementById('edit_inspector').value = inspector;
        document.getElementById('edit_inspected_unit').value = inspectedUnit;
        document.getElementById('edit_inspection_details').value = inspectionDetails;
        document.getElementById('edit_remarks').value = remarks;
        
        // 显示编辑模态框
        document.getElementById('editRecordModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editRecordModal').style.display = 'none';
    }

    function showDeleteConfirm(id) {
        currentDeleteId = id;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        currentDeleteId = null;
    }

    function confirmDelete() {
        if (currentDeleteId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${currentDeleteId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        closeDeleteModal();
    }

    // 点击模态框外部关闭
    window.onclick = function(event) {
        const newRecordModal = document.getElementById('newRecordModal');
        const editRecordModal = document.getElementById('editRecordModal');
        const deleteModal = document.getElementById('deleteModal');
        if (event.target == newRecordModal) {
            closeNewRecordModal();
        }
        if (event.target == editRecordModal) {
            closeEditModal();
        }
        if (event.target == deleteModal) {
            closeDeleteModal();
        }
    }

    // 添加自动隐藏消息的功能
    document.addEventListener('DOMContentLoaded', function() {
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 3000);
        }
    });

    // 在页面加载时自动填充当前时间
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        
        document.getElementById('new_check_time').value = currentDateTime;
    });

    // 主题切换功能
    function toggleTheme() {
        const body = document.body;
        const themeIcon = document.querySelector('#themeToggle .material-icons');
        const themeText = document.querySelector('#themeText');
        
        if (body.classList.contains('light-theme')) {
            body.classList.remove('light-theme');
            themeIcon.textContent = '';
            themeText.textContent = '深色模式';
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.add('light-theme');
            themeIcon.textContent = '';
            themeText.textContent = '浅色模式';
            localStorage.setItem('theme', 'light');
        }
    }

    // 页面加载时检查并应用保存的主题
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        const themeIcon = document.querySelector('#themeToggle .material-icons');
        const themeText = document.querySelector('#themeText');
        
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
            themeIcon.textContent = '';
            themeText.textContent = '浅色模式';
        } else {
            themeIcon.textContent = '';
            themeText.textContent = '深色模式';
        }

        // 其他现有的DOMContentLoaded事件处理程序
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 3000);
        }

        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        
        document.getElementById('new_check_time').value = currentDateTime;
    });
    </script>
    <div class="copyright">
        Copyright © 2025 TingZhou_You 原创开发 版权所有 系统升级请微信联系 TingZhou_You
    </div>
</body>
</html>
