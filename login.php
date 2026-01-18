<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ログイン | まちの目</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: #0f172a; color: white; display: flex; justify-content: center; align-items: center; height: 100vh;">
    <form action="login_act.php" method="post" style="background: rgba(255,255,255,0.1); padding: 40px; border-radius: 20px; width: 300px;">
        <h2 style="text-align: center; margin-bottom: 30px;">Login</h2>
        <div style="margin-bottom: 20px;">
            <label>ID</label><br>
            <input type="text" name="lid" style="width: 100%; padding: 10px; border-radius: 5px;">
        </div>
        <div style="margin-bottom: 30px;">
            <label>Password</label><br>
            <input type="password" name="lpw" style="width: 100%; padding: 10px; border-radius: 5px;">
        </div>
        <button type="submit" class="btn-primary" style="width: 100%;">ログイン</button>
    </form>
</body>
</html>