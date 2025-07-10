<?php
header("Content-Type: application/json");
require 'db.php'; // Ensure this file initializes $pdo correctly

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/')); // Explode path into segments

// Simple router
if ($request[0] === 'tasks') {

    switch ($method) {
        case 'GET':
            if (isset($request[1]) && is_numeric($request[1])) {
                $stmt = $pdo->prepare("SELECT * FROM task WHERE id = ?");
                $stmt->execute([$request[1]]);
                $task = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($task) {
                    echo json_encode($task);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM task");
                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($tasks);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($request[1]) && is_numeric($request[1])) {
                // Update existing task
                $taskId = $request[1];

                $fields = [];
                $params = [];

                if (!empty($data['title'])) {
                    $fields[] = "title = ?";
                    $params[] = $data['title'];
                }
                if (!empty($data['description'])) {
                    $fields[] = "description = ?";
                    $params[] = $data['description'];
                }
                if (!empty($data['status'])) {
                    $fields[] = "status = ?";
                    $params[] = $data['status'];
                }

                if (empty($fields)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No fields to update']);
                    exit;
                }

                $params[] = $taskId;

                $sql = "UPDATE task SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['message' => 'Task updated']);
            } else {
                // Create new task
                if (empty($data['title']) || empty($data['description'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title and description are required']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO task (title, description) VALUES (?, ?)");
                $stmt->execute([$data['title'], $data['description']]);

                echo json_encode(['message' => 'Task created', 'id' => $pdo->lastInsertId()]);
            }
            break;

        case 'DELETE':
            $taskId = $request[1] ?? null;

            if (!$taskId || !is_numeric($taskId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid Task ID']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM task WHERE id = ?");
            $stmt->execute([$taskId]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found']);
            } else {
                echo json_encode(['message' => 'Task deleted successfully']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
