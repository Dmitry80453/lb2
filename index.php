<?php
require_once __DIR__ . "/vendor/autoload.php";

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->dbforlab->schedule;

$group = $_POST['group'] ?? '';
$teacher = $_POST['teacher'] ?? '';
$subject = $_POST['subject'] ?? '';
$room = $_POST['room'] ?? '';

$result = [];
if ($group) {
    $result = $collection->find(['type' => 'LB', 'groups' => $group])->toArray();
} elseif ($teacher && $subject) {
    $result = $collection->find(['type' => 'LK', 'teacher' => $teacher, 'subject' => $subject])->toArray();
} elseif ($room) {
    $result = $collection->find(['room' => $room])->toArray();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Розклад занять</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Розклад занять</h1>
    
    <form method="POST" id="scheduleForm">
        <label>Група (лабораторні): 
            <select name="group">
                <option value="">Виберіть групу</option>
                <option value="KIYKI-22-4">KIYKI-22-4</option>
                <option value="KIYKI-22-5">KIYKI-22-5</option>
            </select>
        </label><br>
        <label>Викладач (лекції): <input type="text" name="teacher"></label><br>
        <label>Дисципліна: <input type="text" name="subject"></label><br>
        <label>Аудиторія: <input type="text" name="room"></label><br>
        <button type="submit">Пошук</button>
    </form>

    <h2>Результати</h2>
    <div id="results">
        <?php if (empty($result)): ?>
            <p>Нічого не знайдено.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Час</th>
                        <th>Аудиторія</th>
                        <th>Предмет</th>
                        <th>Тип</th>
                        <th>Групи</th>
                        <th>Викладач</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $item): ?>
                        <tr>
                            <td><?= $item['date'] ?></td>
                            <td><?= $item['time'] ?></td>
                            <td><?= $item['room'] ?></td>
                            <td><?= $item['subject'] ?></td>
                            <td><?= $item['type'] ?></td>
                            <td><?= implode(', ', (array)$item['groups']) ?></td>
                            <td><?= $item['teacher'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <h2>Збережені результати</h2>
    <div id="savedResults"></div>

    <script>
        const form = document.getElementById('scheduleForm');
        const savedResultsDiv = document.getElementById('savedResults');

        form.addEventListener('submit', function(e) {
            const group = form.group.value;
            const teacher = form.teacher.value;
            const subject = form.subject.value;
            const room = form.room.value;
            const key = `schedule_${group || teacher || room}`;

            const saved = localStorage.getItem(key);
            if (saved) {
                savedResultsDiv.innerHTML = `<p>Збережено раніше: ${saved}</p>`;
            } else {
                savedResultsDiv.innerHTML = '<p>Немає збережених результатів</p>';
            }

            <?php if ($result): ?>
                const currentResult = <?= json_encode($result, JSON_UNESCAPED_UNICODE) ?>;
                localStorage.setItem(key, JSON.stringify(currentResult));
            <?php endif; ?>
        });

        window.onload = function() {
            const group = form.group.value;
            const teacher = form.teacher.value;
            const subject = form.subject.value;
            const room = form.room.value;
            const key = `schedule_${group || teacher || room}`;
            const saved = localStorage.getItem(key);
            savedResultsDiv.innerHTML = saved ? `<p>Збережено раніше: ${saved}</p>` : '<p>Немає збережених результатів</p>';
        };
    </script>
</body>
</html>