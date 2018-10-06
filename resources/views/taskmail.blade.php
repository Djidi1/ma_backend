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
    • Объект: <b>{{ $object['title'] }}</b><br/>
    • Требование: <b>{{ $requirement['title'] }}</b><br/>
    • Описание: <b>{{ $comment }}</b><br/>
    • Задача: <a href="{{ env('APP_URL') }}/#/tasks_list/{{ $task_id }}">устранение несоответствия №{{ $task_id }}</a>
</body>
</html>
