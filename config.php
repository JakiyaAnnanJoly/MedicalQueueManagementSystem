<?php

declare(strict_types=1);

$geminiApiKey = getenv('GEMINI_API_KEY') ?: 'AIzaSyBKF-7mn1-821rsRTydvizSyvM6-A6fkJ8';
$requestedAiMode = getenv('AI_MODE');
$defaultAiMode = $geminiApiKey !== '' ? 'gemini' : 'mock';
$aiMode = is_string($requestedAiMode) && $requestedAiMode !== '' ? $requestedAiMode : $defaultAiMode;

return [
    'app_timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Dhaka',
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_name' => getenv('DB_NAME') ?: 'mediqueue_ai',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    'db_charset' => 'utf8mb4',
    'ai_mode' => $aiMode,

    // Gemini provider config
    'gemini_api_key' => $geminiApiKey,
    'gemini_model' => getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash-lite',
    'gemini_api_version' => getenv('GEMINI_API_VERSION') ?: 'v1beta',
];
