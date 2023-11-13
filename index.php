<?php
$filename_todo = 'tasks.txt';
$filename_completed = 'completed_tasks.txt';

//Check if the file exists; if not, create it
if (!file_exists($filename_todo)) {
    fopen($filename_todo, 'w');
}

if (!file_exists($filename_completed)) {
    fopen($filename_completed, 'w');
}

$tasks_todo = [];
$tasks_completed = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        //Get the task, date, and time from the form
        $task = $_POST['task'];
        $date = $_POST['date'];
        $time = $_POST['time'];

        //Validate the task
        if (!empty($task)) {
            //Append the task, date, and time to the to-do list file
            file_put_contents($filename_todo, $task . ' - Due: ' . $date . ' ' . $time . PHP_EOL, FILE_APPEND);

            //Schedule a notification
            echo "<script>scheduleNotification('$task', '$date', '$time');
            function scheduleNotification(task, date, time) {
                // Parse the date and time
                const dueDateTime = new Date(`${date} ${time}`);
    
                // Check if the due date and time are in the future
                if (dueDateTime > new Date()) {
                    // Show an alert
                    setTimeout(() => {
                        alert(`Task Due:\nTask: ${task}\nDue: ${date} ${time}`);
                    }, dueDateTime - new Date());
                }
            }
            </script>";
        }
    } elseif (isset($_POST['complete'])) {
        $completedTaskIndex = $_POST['completedTaskIndex'];

        $tasks = file($filename_todo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        //Check if the index is valid
        if (isset($tasks[$completedTaskIndex])) {
            $completedTask = $tasks[$completedTaskIndex];

            //Remove the completed task from the to-do list
            unset($tasks[$completedTaskIndex]);

            //Write the updated to-do list
            file_put_contents($filename_todo, implode(PHP_EOL, $tasks));

            //Append the completed task to the completed tasks list
            file_put_contents($filename_completed, $completedTask . PHP_EOL, FILE_APPEND);
        }
    } elseif (isset($_POST['delete'])) {
        $deletedTaskIndex = $_POST['deletedTaskIndex'];

        $completedTasks = file($filename_completed, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (isset($completedTasks[$deletedTaskIndex])) {
            //Remove the completed task from the completed tasks list
            unset($completedTasks[$deletedTaskIndex]);

            //Write the updated completed tasks list
            file_put_contents($filename_completed, implode(PHP_EOL, $completedTasks));
        }
    } elseif (isset($_POST['delete_all'])) {
        //Delete all completed tasks
        file_put_contents($filename_completed, '');
    }
}

//Read tasks from the to-do list file
$tasks_todo = file($filename_todo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

//Read tasks from the completed tasks list file
$tasks_completed = file($filename_completed, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP To-Do List</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h1 {
            text-align: center;
            color: #555;
        }

        h2 {
            color: #555;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #fff;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80%;

        }

        button {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c82333;
        }

        form {
            margin-top: 20px;
            width: 80%;

        }

        form label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        form button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 3px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>


<body>
    <h1>To-Do List</h1>

    <!-- Display tasks -->
    <h2>To-Do</h2>
    <ul>
        <?php
        if (is_array($tasks_todo)) {
            foreach ($tasks_todo as $index => $task) :
        ?>
                <li>
                    <?= htmlspecialchars($task); ?>
                    <form method="post" action="">
                        <input type="hidden" name="completedTaskIndex" value="<?= $index; ?>">
                        <button type="submit" name="complete">Complete</button>
                    </form>
                </li>
        <?php
            endforeach;
        } else {
            echo "Error reading tasks file.";
        }
        ?>
    </ul>

    <!-- Completed tasks -->
    <h2>Completed</h2>
    <ul>
        <?php
        if (is_array($tasks_completed)) {
            foreach ($tasks_completed as $index => $completedTask) :
        ?>
                <li>
                    <?= htmlspecialchars($completedTask); ?>
                    <form method="post" action="">
                        <input type="hidden" name="deletedTaskIndex" value="<?= $index; ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </li>
        <?php
            endforeach;
        } else {
            echo "Error reading completed tasks file.";
        }
        ?>
    </ul>

    <!-- Delete all completed tasks button -->
    <form method="post" action="">
        <button type="submit" name="delete_all">Delete All Completed Tasks</button>
    </form>

    <!-- Form to add a new task -->
    <form method="post" action="">
        <label for="task">Add Task:</label>
        <input type="text" id="task" name="task" required>
        <label for="date">Due Date:</label>
        <input type="date" id="date" name="date" required>
        <label for="time">Due Time:</label>
        <input type="time" id="time" name="time" required>
        <button type="submit" name="add">Add</button>
    </form>

</body>

</html>