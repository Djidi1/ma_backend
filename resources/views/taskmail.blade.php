<!DOCTYPE html>
<html>
<head>
    <title>Система гарантий качества</title>
</head>
<body>
    <h2>У вас есть новая задача</h2>
    <br/>
    • Номер задачи: <b>{{ $task_id }}</b><br/>
    • Назначено: <b>{{ $user['name'] }}</b><br/>
    • Дата выполнения: <b>{{ $end_date }}</b><br/>
    • Описание: <b>{{ $comment }}</b>
</body>
</html>