<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
</head>
<body>
    <h2>{{ $body }}</h2>
    <br/>
    • Номер задачи: <b>{{ $task_id }}</b><br/>
    • Назначено: <b>{{ $user['name'] }}</b><br/>
    • Дата выполнения: <b>{{ $end_date }}</b><br/>
    • Описание: <b>{{ $comment }}</b>
</body>
</html>