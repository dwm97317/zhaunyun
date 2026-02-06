<?php
// web/tdd_ditch_tool.php

/**
 * TDD Tool for Verifying Ditch Sender JSON Persistence
 * 
 * Usage: php -d session.auto_start=0 -d session.use_cookies=0 web/tdd_ditch_tool.php
 */

// 1. Environment & Configuration
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_STRICT);

define('WEB_PATH', __DIR__ . '/');
define('APP_PATH', WEB_PATH . '../source/application/');
define('ROOT_PATH', WEB_PATH . '../');

// Load ThinkPHP
require APP_PATH . '../thinkphp/base.php';

// 2. Initialize Framework (no common init to avoid vendor dependencies)

use app\store\model\Ditch;
use app\common\model\BaseModel;
use think\Db;
use think\Cache;

echo "\n=======================================================\n";
echo "      TDD TOOL: DITCH SENDER_JSON SAVING CHECK       \n";
echo "=======================================================\n";

// Manual Mock for BaseModel params if needed (Project specific)
// If BaseModel relies on Session, we might need to inject static props
// Based on typical Yoshop logic:
if (property_exists(BaseModel::class, 'wxapp_id')) {
    // Using reflection or just assuming accessible if public/protected static
    // Since we can't easily access protected static, we assume allowField(true) logic handles itself
    // unless 'add' method explicitly uses self::$wxapp_id.
    // We'll see if Add fails.
}

// --- CHECK 1: Database Schema ---
echo "\n[Step 1] Verifying Database Schema...\n";
try {
    $cols = Db::query("SHOW COLUMNS FROM yoshop_ditch LIKE 'sender_json'");
    if (empty($cols)) {
        echo " [FAIL] Column 'sender_json' NOT FOUND in table 'yoshop_ditch'.\n";
        echo "        Action: Review migration/SQL steps.\n";
        exit(1);
    }
    echo " [PASS] Column 'sender_json' exists (Type: " . $cols[0]['Type'] . ").\n";
} catch (\Throwable $e) {
    echo " [FAIL] Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

// --- CHECK 2: Schema Cache (The likely culprit) ---
echo "\n[Step 2] Cleaning Schema Cache...\n";
$runtimePath = ROOT_PATH . 'runtime';
$schemaPath = $runtimePath . '/schema';
$cleared = 0;

if (is_dir($schemaPath)) {
    $files = glob($schemaPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
            $cleared++;
        }
    }
}
// Also clear global cache via framework
try {
    Cache::clear(); 
} catch(\Exception $e) {}

if ($cleared > 0) {
    echo " [INFO] Cleared $cleared schema cache file(s).\n";
} else {
    echo " [INFO] No file-based schema cache found (Clean).\n";
}
echo " [PASS] Cache environment ready.\n";


// --- CHECK 3: EDIT Functionality (Update existing) ---
echo "\n[Step 3] Testing 'EDIT' Logic (Model->save)...\n";
// Find a target ditch
$ditch = Ditch::get(10075);
if (!$ditch) {
    $ditch = Ditch::order('ditch_id', 'desc')->find();
}

if (!$ditch) {
    echo " [FAIL] No Ditch records found to test.\n";
    exit(1);
}

$targetId = $ditch['ditch_id'];
echo " Target ID: $targetId\n";

// Prepare JSON Data
$editData = [
    'name' => 'TDD_EDIT_USER',
    'phone' => '13888888888',
    'province' => 'TestProv_' . time(),
    'city' => 'TestCity',
    'district' => 'TestDist',
    'address' => 'TestAddr 123'
];
$jsonStr = json_encode($editData, JSON_UNESCAPED_UNICODE);

// Execute Update
// Mimic Controller: returns boolean or int
$res = $ditch->allowField(true)->save(['sender_json' => $jsonStr]);

if ($res === false) {
    echo " [FAIL] Save returned false. Reason: " . $ditch->getError() . "\n";
} else {
    // Verify Persistence
    $checkDitch = Ditch::get($targetId);
    if ($checkDitch['sender_json'] === $jsonStr) {
        echo " [PASS] Successfully updated 'sender_json'.\n";
        echo "        Data in DB: " . substr($checkDitch['sender_json'], 0, 60) . "...\n";
    } else {
        echo " [FAIL] Save reported success, but data mismatch!\n";
        echo "        Sent: " . $jsonStr . "\n";
        echo "        Read: " . $checkDitch['sender_json'] . "\n";
    }
}

// --- CHECK 4: ADD Functionality (Create new) ---
echo "\n[Step 4] Testing 'ADD' Logic (Model->create)...\n";
$addData = [
    'ditch_name' => 'TDD_Test_Channel_' . rand(100,999),
    'ditch_no'   => 'TDD' . rand(1000,9999), 
    'type'       => 1, // Kuaidi100
    'ditch_type' => 1, // Default
    'sort'       => 999,
    'sender_json'=> $jsonStr,
    'wxapp_id'   => 10001 // Explicitly providing this might be needed if Session invalid
];

// If Ditch model overrides add(), we should call that if possible, 
// OR call parent::create() / save() directly to test Model capability.
// Controller calls $model->add($data).
// Tricky: Controller injects wxapp_id? No, Model usually takes it from static context.
// Let's rely on standard save().

$newDitch = new Ditch();
try {
    // We try to save. If it fails due to wxapp_id missing in session, we catch it.
    // We set data manually first
    $resAdd = $newDitch->allowField(true)->save($addData);
    
    if ($resAdd) {
        $newId = $newDitch->ditch_id;
        echo " [PASS] Created new record ID: $newId\n";
        
        // Verify
        $verifyAdd = Ditch::get($newId);
        if ($verifyAdd['sender_json'] === $jsonStr) {
            echo " [PASS] 'sender_json' correctly saved on create.\n";
        } else {
            echo " [FAIL] New record created, but 'sender_json' is empty/wrong.\n";
        }
        
        // Cleanup
        $newDitch->delete();
        echo " [INFO] Cleaned up test record.\n";
        
    } else {
        echo " [FAIL] Create failed. Error: " . $newDitch->getError() . "\n";
        if (strpos($newDitch->getError(), 'wxapp_id') !== false) {
             echo "        (Likely missing Session content for Wxapp ID in CLI mode)\n";
        }
    }
    
} catch (\Throwable $e) {
    echo " [FAIL] Exception during Add: " . $e->getMessage() . "\n";
}

echo "\n=======================================================\n";
echo " TDD COMPLETE. If [Step 2] cleared cache, try Web UI now.\n";
echo "=======================================================\n";
