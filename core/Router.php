<?php

namespace Core;

class Router
{
    public function dispatch(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            http_response_code(405);
            echo json_encode(['error' => 'Only POST requests are allowed']);
            return;
        }

        // Get the controller and action from query parameters
        $controllerName = $_GET['controller'] ?? null;
        $actionName = $_GET['action'] ?? null;

        if ($controllerName && $actionName) {
            $this->invokeController($controllerName, $actionName);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Missing request parameters']);
        }
    }

    private function invokeController(string $controller, string $action): void
    {
        $controllerClass = "App\\Controllers\\{$controller}Controller";

        if (class_exists($controllerClass)) {
            $controllerInstance = DIContainer::resolve($controllerClass);
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Controller not found']);
        }
    }
}