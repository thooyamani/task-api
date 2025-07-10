# task-api
Build a RESTful Task Management API
Test the API:
List tasks: GET http://localhost:8000/api.php/tasks

Get task by ID: GET http://localhost:8000/api.php/tasks/1

Create task: POST http://localhost:8000/api.php/tasks (Body: { "title": "Test", "description": "Sample" })

Update task: POST http://localhost:8000/api.php/tasks/1 (Body: { "status": "completed" })

Delete task: DELETE http://localhost:8000/api.php/tasks/1
