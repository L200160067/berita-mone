<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Checking config...\n";
$repo = config('services.github_asset.repo');
$token = config('services.github_asset.token');
$branch = config('services.github_asset.branch', 'main');

echo "Repo: " . ($repo ?: '[EMPTY]') . "\n";
echo "Token: " . ($token ? '********' . substr($token, -4) : '[EMPTY]') . "\n";
echo "Branch: " . $branch . "\n";

if (empty($repo) || empty($token)) {
    echo "\nERROR: Config is empty. Cannot test GitHub upload.\n";
    exit(1);
}

echo "\nCreating a dummy file to test upload...\n";
$filename = 'test_' . time() . '.txt';
$content = "This is a test file uploaded from Laravel script at " . date('Y-m-d H:i:s');
$base64 = base64_encode($content);
$path = "images/blog/" . $filename;

echo "Uploading to https://api.github.com/repos/{$repo}/contents/{$path}...\n";

$response = Http::withToken($token)
    ->put("https://api.github.com/repos/{$repo}/contents/{$path}", [
        'message' => "Test upload from CLI",
        'content' => $base64,
        'branch' => $branch,
    ]);

if ($response->successful()) {
    echo "\n✅ SUCCESS!\n";
    echo "Raw URL: https://raw.githubusercontent.com/{$repo}/{$branch}/{$path}\n";
} else {
    echo "\n❌ FAILED!\n";
    echo "Status code: " . $response->status() . "\n";
    echo "Response body: \n";
    print_r($response->json());
}
