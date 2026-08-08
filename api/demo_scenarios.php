<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../auth.php';
require_roles_json(['admin', 'patient']);


$scenarios = require __DIR__ . '/../data/demo_scenarios.php';


$quickPrompts = [];
foreach ($scenarios as $scenario) {
    $prompt = trim((string) ($scenario['prompt'] ?? ''));
    if ($prompt === '') {
        $prompt = trim((string) ($scenario['symptoms'] ?? ''));
    }
    if ($prompt === '') {
        continue;
    }
    if (!in_array($prompt, $quickPrompts, true)) {
        $quickPrompts[] = $prompt;
    }
    if (count($quickPrompts) >= 10) {
        break;
    }
}


echo json_encode([
    'scenarios' => $scenarios,
    'count' => count($scenarios),
    'quick_prompts' => $quickPrompts,
    'generated_at' => (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM),
]);



