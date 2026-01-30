<?php
// web/tdd_ditch_save.php

// Define paths
define('WEB_PATH', __DIR__ . '/');
define('APP_PATH', WEB_PATH . '../source/application/');

// Load framework
require APP_PATH . '../thinkphp/base.php';

// Skip common init to avoid vendor dependencies

use app\store\model\Ditch;
use think\Db;

echo "--- TDD: Testing Ditch Model Save ---\n";

try {
    // 1. Verify DB Connection and Column
    $sql = "SHOW COLUMNS FROM yoshop_ditch LIKE 'sender_json'";
    $res = Db::query($sql);
    if (empty($res)) {
        die("FATAL: Column sender_json does not exist in yoshop_ditch table.\n");
    }
    echo "Column 'sender_json' exists.\n";

    // 2. Load Ditch 10075
    $ditch = Ditch::get(10075);
    if (!$ditch) {
        die("FATAL: Ditch 10075 not found.\n");
    }
    echo "Loaded Ditch: " . $ditch['ditch_name'] . "\n";
    echo "Current sender_json: " . ($ditch['sender_json'] ?: '(empty)') . "\n";

    // 3. Attempt Save
    $testData = [
        'name' => 'TDD_User',
        'province' => 'TestProv',
        'ts' => time()
    ];
    $json = json_encode($testData);
    
    echo "Attempting to save sender_json = $json ...\n";
    
    // Determine if we need to call allowField(true)
    // The Controller calls $model->edit($data), which calls $this->allowField(true)->save($data)
    
    $updateData = ['sender_json' => $json];
    
    // We simulate what the Model->edit() does:
    // ref: source/application/store/model/Ditch.php -> edit()
    $result = $ditch->allowField(true)->save($updateData);

    if ($result === false) {
        echo "Save FAILED. Error: " . $ditch->getError() . "\n";
    } else {
        echo "Save returned: " . ($result) . "\n";
        
        // 4. Re-read to confirm persistence
        $check = Ditch::get(10075); // Get fresh
        echo "Re-read sender_json: " . $check['sender_json'] . "\n";
        
        if ($check['sender_json'] === $json) {
            echo "SUCCESS: Data persisted correctly.\n";
        } else {
            echo "FAILURE: Data mismatch. Cache issue or Field filtering.\n";
            // Check if it's explicitly null
            if (is_null($check['sender_json'])) {
                echo "Field is NULL in DB.\n";
            }
        }
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
