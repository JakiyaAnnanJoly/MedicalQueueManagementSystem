<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../auth.php';
require __DIR__ . '/../db.php';
require_login_json();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$config = require __DIR__ . '/../config.php';
$dataset = require __DIR__ . '/../data/mock_ai_dataset.php';
$payload = json_decode((string) file_get_contents('php://input'), true);
$message = trim((string) ($payload['message'] ?? ''));
$requestedMode = trim((string) ($payload['mode'] ?? ''));
$stream = (bool) ($payload['stream'] ?? false);


if ($message === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Message is required']);
    exit;
}


$mode = $requestedMode !== '' ? $requestedMode : (string) ($config['ai_mode'] ?? 'mock');
$mode = in_array($mode, ['mock', 'gemini'], true) ? $mode : 'mock';


$systemPrompt = <<<PROMPT
Role: You are a Health Information Assistant. Your sole purpose is to provide educational information regarding medical conditions, symptoms, and general wellness.


Strict Content Filter:


Medical Only: If a user asks about anything non-medical (history, math, coding, personal advice, etc.), respond with: "I am a health information assistant. I only provide medical and health-related information."


No Diagnosis: Do not say "You have [Disease]." Instead, say "Your symptoms are commonly associated with [Condition], but only a doctor can confirm this."


Emergency Protocol: If a user mentions life-threatening symptoms (chest pain, difficulty breathing, severe bleeding), your first sentence must be: "If this is a medical emergency, please contact your local emergency services immediately."


Output Style: Use bullet points for symptoms and clear headings for readability.
PROMPT;
$systemPrompt .= "\n\n" . buildDepartmentContextForPrompt();


if ($stream) {
    streamAssistant($mode, $config, $dataset, $systemPrompt, $message);
    exit;
}


$result = resolveAssistant($mode, $config, $dataset, $systemPrompt, $message);
if (isset($result['error'])) {
    http_response_code(502);
}
echo json_encode($result);


function streamAssistant(string $mode, array $config, array $dataset, string $systemPrompt, string $message): void
{
    beginSse();
    sseEvent('status', ['message' => 'thinking']);


    if ($mode === 'mock') {
        sseEvent('final', matchFromDataset($dataset, $message));
        return;
    }


    if ($mode === 'gemini') {
        $streamed = streamFromGemini($config, $systemPrompt, $message);
        if ($streamed['ok']) {
            sseEvent('final', [
                'reply' => sanitizeAssistantReply((string) $streamed['text']),
                'provider' => 'gemini',
                'mode' => 'gemini',
            ]);
            return;
        }
        sseEvent('error', ['error' => (string) ($streamed['error'] ?? 'Gemini failed to answer.')]);
        return;
    }


    sseEvent('error', ['error' => 'Unsupported assistant mode']);
}


function resolveAssistant(string $mode, array $config, array $dataset, string $systemPrompt, string $message): array
{
    if ($mode === 'mock') {
        return matchFromDataset($dataset, $message);
    }


    if ($mode === 'gemini') {
        $gemini = askGeminiDetailed($config, $systemPrompt, $message);
        if ($gemini['ok']) {
            return ['reply' => sanitizeAssistantReply((string) $gemini['text']), 'provider' => 'gemini', 'mode' => 'gemini'];
        }
        return ['error' => (string) $gemini['error']];
    }


    return ['error' => 'Unsupported assistant mode'];
}


function beginSse(): void
{
    if (!headers_sent()) {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
    }


    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    ob_implicit_flush(true);
}


function sseEvent(string $event, array $payload): void
{
    echo "event: {$event}\n";
    echo 'data: ' . json_encode($payload) . "\n\n";
    flush();
}


function streamFromGemini(array $config, string $systemPrompt, string $message): array
{
    $apiKey = trim((string) ($config['gemini_api_key'] ?? ''));
    if ($apiKey === '') {
        return ['ok' => false, 'text' => '', 'error' => 'Gemini API key is missing. Set GEMINI_API_KEY.'];
    }


    $payload = [
        'systemInstruction' => [
            'parts' => [
                ['text' => $systemPrompt],
            ],
        ],
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $message],
                ],
            ],
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 220,
        ],
    ];


    $version = geminiApiVersion($config);
    $model = normalizeGeminiModel((string) ($config['gemini_model'] ?? 'gemini-2.0-flash'));
    $attempt = executeGeminiStream($apiKey, $version, $model, $payload);
    if ($attempt['ok']) {
        return $attempt;
    }


    if (shouldRetryGeminiModel((string) $attempt['error'])) {
        $fallback = chooseFallbackGeminiModel($apiKey, 'streamGenerateContent', $model);
        if ($fallback !== null) {
            $attempt2 = executeGeminiStream($apiKey, (string) $fallback['version'], (string) $fallback['model'], $payload);
            if ($attempt2['ok']) {
                return $attempt2;
            }
            $attempt = $attempt2;
        }
    }


    return $attempt;
}


function executeGeminiStream(string $apiKey, string $version, string $model, array $payload): array
{
    $url = buildGeminiUrl($apiKey, $version, $model, 'streamGenerateContent', true);
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'text' => '', 'error' => 'Failed to initialize Gemini request.'];
    }


    $buffer = '';
    $fullText = '';
    $hadChunk = false;
    $rawBody = '';


    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_WRITEFUNCTION => static function ($ch, string $chunk) use (&$buffer, &$fullText, &$hadChunk, &$rawBody): int {
            $rawBody .= $chunk;
            $buffer .= $chunk;


            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = (string) substr($buffer, $pos + 1);


                if (!str_starts_with($line, 'data:')) {
                    continue;
                }


                $data = trim(substr($line, 5));
                if ($data === '' || $data === '[DONE]') {
                    continue;
                }


                $json = json_decode($data, true);
                $piece = (string) ($json['candidates'][0]['content']['parts'][0]['text'] ?? '');
                if ($piece === '') {
                    continue;
                }


                $hadChunk = true;
                $fullText .= $piece;
                sseEvent('delta', ['text' => $piece]);
            }


            return strlen($chunk);
        },
    ]);


    $ok = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = $ok === false ? (string) curl_error($ch) : '';


    if ($code >= 400 || !$hadChunk || trim($fullText) === '') {
        $error = $curlError !== '' ? $curlError : extractProviderError($rawBody, 'Gemini streaming failed to return a response.');
        return ['ok' => false, 'text' => '', 'error' => $error];
    }


    return ['ok' => true, 'text' => trim($fullText), 'error' => ''];
}


function askGeminiDetailed(array $config, string $systemPrompt, string $message): array
{
    $apiKey = trim((string) ($config['gemini_api_key'] ?? ''));
    if ($apiKey === '') {
        return ['ok' => false, 'text' => '', 'error' => 'Gemini API key is missing. Set GEMINI_API_KEY.'];
    }


    $payload = [
        'systemInstruction' => [
            'parts' => [
                ['text' => $systemPrompt],
            ],
        ],
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $message],
                ],
            ],
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 220,
        ],
    ];


    $version = geminiApiVersion($config);
    $model = normalizeGeminiModel((string) ($config['gemini_model'] ?? 'gemini-2.0-flash'));
    $attempt = executeGeminiGenerate($apiKey, $version, $model, $payload);
    if ($attempt['ok']) {
        return $attempt;
    }


    if (shouldRetryGeminiModel((string) $attempt['error'])) {
        $fallback = chooseFallbackGeminiModel($apiKey, 'generateContent', $model);
        if ($fallback !== null) {
            $attempt2 = executeGeminiGenerate($apiKey, (string) $fallback['version'], (string) $fallback['model'], $payload);
            if ($attempt2['ok']) {
                return $attempt2;
            }
            $attempt = $attempt2;
        }
    }


    return $attempt;
}


function executeGeminiGenerate(string $apiKey, string $version, string $model, array $payload): array
{
    $url = buildGeminiUrl($apiKey, $version, $model, 'generateContent', false);
    $result = postJson($url, $payload, []);
    if (!$result) {
        return ['ok' => false, 'text' => '', 'error' => 'Gemini request failed.'];
    }
    if (($result['code'] ?? 0) >= 400) {
        return [
            'ok' => false,
            'text' => '',
            'error' => extractProviderError((string) ($result['body'] ?? ''), 'Gemini request failed.'),
        ];
    }


    $json = json_decode((string) ($result['body'] ?? ''), true);
    $text = trim((string) ($json['candidates'][0]['content']['parts'][0]['text'] ?? ''));


    if ($text === '') {
        return ['ok' => false, 'text' => '', 'error' => 'Gemini returned an empty response.'];
    }


    return ['ok' => true, 'text' => $text, 'error' => ''];
}


function matchFromDataset(array $dataset, string $message): array
{
    $text = strtolower($message);


    foreach ($dataset as $row) {
        foreach (($row['keywords'] ?? []) as $keyword) {
            if ($keyword !== '' && str_contains($text, strtolower((string) $keyword))) {
                return [
                    'reply' => (string) $row['reply'],
                    'provider' => 'mock-dataset',
                    'mode' => 'mock',
                    'match_id' => (string) $row['id'],
                    'suggested_department' => (string) $row['department'],
                    'suggested_priority' => (int) $row['priority'],
                ];
            }
        }
    }


    return [
        'reply' => 'For booking help, share your symptoms, preferred date, and location. I will suggest a department and priority score.',
        'provider' => 'mock-dataset',
        'mode' => 'mock',
        'match_id' => 'default_help',
        'suggested_department' => 'General Checkup',
        'suggested_priority' => 1,
    ];
}


function postJson(string $url, array $payload, array $headers): ?array
{
    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }


    $defaultHeaders = ['Content-Type: application/json'];


    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);


    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = $body === false ? (string) curl_error($ch) : '';


    if ($body === false) {
        return ['code' => $code, 'body' => '', 'curl_error' => $curlError];
    }


    return ['code' => $code, 'body' => $body, 'curl_error' => $curlError];
}


function extractProviderError(string $body, string $fallback): string
{
    if ($body === '') {
        return $fallback;
    }


    $json = json_decode($body, true);
    if (is_array($json)) {
        $msg = (string) ($json['error']['message'] ?? $json['message'] ?? '');
        if ($msg !== '') {
            return $msg;
        }
    }


    if (preg_match('/"message"\s*:\s*"([^"]+)"/', $body, $m)) {
        return stripcslashes((string) $m[1]);
    }


    return $fallback;
}


function sanitizeAssistantReply(string $text): string
{
    $clean = preg_replace('/\bthis information is for educational purposes only\.?\b/i', '', $text);
    $clean = preg_replace('/\bfor educational purposes only\.?\b/i', '', (string) $clean);
    $clean = preg_replace('/[ \t]+\n/', "\n", (string) $clean);
    $clean = preg_replace("/\n{3,}/", "\n\n", (string) $clean);
    return trim((string) $clean);
}


function geminiApiVersion(array $config): string
{
    $v = trim((string) ($config['gemini_api_version'] ?? 'v1beta'));
    return in_array($v, ['v1beta', 'v1'], true) ? $v : 'v1beta';
}


function normalizeGeminiModel(string $model): string
{
    $m = trim($model);
    if ($m === '') {
        return 'gemini-2.0-flash';
    }
    return str_starts_with($m, 'models/') ? substr($m, 7) : $m;
}


function buildGeminiUrl(string $apiKey, string $version, string $model, string $method, bool $stream): string
{
    $suffix = $stream ? ':streamGenerateContent?alt=sse&key=' : ':generateContent?key=';
    if ($method !== 'streamGenerateContent') {
        $suffix = ':generateContent?key=';
    }
    return 'https://generativelanguage.googleapis.com/' . $version . '/models/'
        . rawurlencode($model)
        . $suffix
        . rawurlencode($apiKey);
}


function shouldRetryGeminiModel(string $error): bool
{
    $e = strtolower($error);
    return str_contains($e, 'not found') || str_contains($e, 'not supported') || str_contains($e, 'permission denied');
}


function chooseFallbackGeminiModel(string $apiKey, string $method, string $excludeModel): ?array
{
    $versions = ['v1beta', 'v1'];
    $candidates = [];


    foreach ($versions as $version) {
        $url = 'https://generativelanguage.googleapis.com/' . $version . '/models?key=' . rawurlencode($apiKey);
        $res = getJson($url);
        if (!$res || ($res['code'] ?? 0) >= 400) {
            continue;
        }
        $json = json_decode((string) ($res['body'] ?? ''), true);
        foreach (($json['models'] ?? []) as $row) {
            $supported = $row['supportedGenerationMethods'] ?? [];
            if (!is_array($supported) || !in_array($method, $supported, true)) {
                continue;
            }


            $name = normalizeGeminiModel((string) ($row['name'] ?? ''));
            if ($name === '' || $name === $excludeModel) {
                continue;
            }
            $candidates[] = ['version' => $version, 'model' => $name];
        }
    }


    if ($candidates === []) {
        return null;
    }


    usort($candidates, static function (array $a, array $b): int {
        $aFlash = str_contains((string) $a['model'], 'flash') ? 1 : 0;
        $bFlash = str_contains((string) $b['model'], 'flash') ? 1 : 0;
        if ($aFlash !== $bFlash) {
            return $bFlash <=> $aFlash;
        }
        return strcmp((string) $b['model'], (string) $a['model']);
    });


    return $candidates[0];
}


function getJson(string $url): ?array
{
    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }


    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPGET => true,
    ]);


    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($body === false) {
        return null;
    }
    return ['code' => $code, 'body' => $body];
}


function buildDepartmentContextForPrompt(): string
{
    $departments = [];


    try {
        $pdo = db();
        $stmt = $pdo->query('SELECT name FROM departments WHERE is_active = 1 ORDER BY name ASC');
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $departments[] = $name;
            }
        }
    } catch (Throwable $e) {
        $departments = [];
    }


    if ($departments === []) {
        return 'Clinic Departments: Department list unavailable right now. If unsure, suggest General Checkup.';
    }


    return 'Clinic Departments (use only these names when suggesting where to book): ' . implode(', ', $departments) . '.';
}



