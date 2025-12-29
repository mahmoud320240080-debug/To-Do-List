<?php
/**
 * ============================================
 * TASKMASTER - API RESPONSE HELPER
 * ============================================
 */

class Response {
    /**
     * Send JSON response
     * @param int $statusCode
     * @param array $data
     */
    public static function json($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Success response
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        self::json($statusCode, [
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Created response
     * @param mixed $data
     * @param string $message
     */
    public static function created($data = null, $message = 'Created successfully') {
        self::success($data, $message, 201);
    }
    
    /**
     * Error response
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     */
    public static function error($message = 'An error occurred', $statusCode = 400, $errors = []) {
        self::json($statusCode, [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ]);
    }
    
    /**
     * Not found response
     * @param string $message
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }
    
    /**
     * Unauthorized response
     * @param string $message
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }
    
    /**
     * Forbidden response
     * @param string $message
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }
    
    /**
     * Validation error response
     * @param array $errors
     */
    public static function validationError($errors) {
        self::error('Validation failed', 422, $errors);
    }
    
    /**
     * Server error response
     * @param string $message
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
}