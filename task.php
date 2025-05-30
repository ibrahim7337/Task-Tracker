<?php

define("TASK_FILE", __DIR__ . "/tasks.json");

function loadTasks()
{
    if (!file_exists(TASK_FILE)) {
        return [];
    }
    $json = file_get_contents(TASK_FILE);
    return json_decode($json, true);
}

function saveTasks($tasks)
{
    $json = json_encode($tasks, JSON_PRETTY_PRINT);
    file_put_contents(TASK_FILE, $json);
}

function add($description)
{
    $tasks = loadTasks();
    $ids = array_column($tasks, 'id');
    $id = empty($ids) ? 1 : max($ids) + 1;
    $now = date('c');

    $tasks[] = [
        'id' => $id,
        'description' => $description,
        'status' => "todo",
        'created_at' => $now,
        'updated_at' => $now,
    ];

    saveTasks($tasks);
    echo "Task added (ID: $id)\n";
}

function update($id, $description)
{
    $tasks = loadTasks();
    $now = date('c');
    $found = false;

    foreach ($tasks as &$task) {
        if ($task['id'] == $id) {
            $task['description'] = $description;
            $task['updated_at'] = $now;
            $found = true;
            break;
        }
    }

    if ($found) {
        saveTasks($tasks);
        echo "Task updated (ID: $id)\n";
    } else {
        echo "Task with ID $id not found.\n";
    }
}

function delete($id)
{
    $tasks = loadTasks();
    $found = false;

    foreach ($tasks as $index => $task) {
        if ($task['id'] == $id) {
            unset($tasks[$index]); // Remove the task entirely
            $found = true;
            break;
        }
    }

    if ($found) {
        $tasks = array_values($tasks); // Reindex to keep array clean
        saveTasks($tasks);
        echo "Task deleted (ID: $id)\n";
    } else {
        echo "Task with ID $id not found.\n";
    }
}

function mark_in_progress($id)
{
    $tasks = loadTasks();
    $found = false;

    foreach ($tasks as &$task) {
        if (!is_array($task) || !isset($task['id'])) {
            continue;
        }
        if ($task['id'] == $id) {
            $task['status'] = "in-progress";
            $found = true;
            break;
        }
    }

    if ($found) {
        saveTasks($tasks);
        echo "Task marked in-progress (ID: $id)\n";
    } else {
        echo "Task with ID $id not found.\n";
    }
}

function mark_done($id)
{
    $tasks = loadTasks();
    $found = false;

    foreach ($tasks as &$task) {
        if (!is_array($task) || !isset($task['id'])) {
            continue;
        }
        if ($task['id'] == $id) {
            $task['status'] = "done";
            $found = true;
            break;
        }
    }

    if ($found) {
        saveTasks($tasks);
        echo "Task marked done (ID: $id)\n";
    } else {
        echo "Task with ID $id not found.\n";
    }
}

function list_tasks($filter = null)
{
    $tasks = loadTasks();

    foreach ($tasks as $task) {
        if (!is_array($task) || !isset($task['id'])) {
            continue;
        }
        if ($filter === null || $task['status'] === $filter) {
            echo "ID: {$task['id']}\n";
            echo "Description: {$task['description']}\n";
            echo "Status: {$task['status']}\n";
            echo "Created At: {$task['created_at']}\n";
            echo "Updated At: {$task['updated_at']}\n";
            echo str_repeat("-", 20) . "\n";
        }
    }
}


$command = $argv[1];

switch ($command) {
    case 'add':
        if (!isset($argv[2])) {
            echo "Task description required.\n";
            exit(1);
        }
        add($argv[2]);
        break;

    case 'update':
        if (!isset($argv[2], $argv[3])) {
            echo "Error: Task ID and new description required.\n";
            exit(1);
        }
        update((int) $argv[2], $argv[3]);
        break;

    case 'delete':
        if (!isset($argv[2])) {
            echo "Error: Task ID required.\n";
            exit(1);
        }
        delete((int) $argv[2]);
        break;

    case 'mark_in_progress':
        if (!isset($argv[2])) {
            echo "Error: Task ID required.\n";
            exit(1);
        }
        mark_in_progress((int) $argv[2]);
        break;

    case 'mark_done':
        if (!isset($argv[2])) {
            echo "Error: Task ID required.\n";
            exit(1);
        }
        mark_done((int) $argv[2]);
        break;

    case 'list':
        $filter = $argv[2] ?? null;
        list_tasks($filter);
        break;

    default:
        echo "Unknown command: $command\n";
        break;
}