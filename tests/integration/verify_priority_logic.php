<?php
/**
 * Test priority matching logic for goodsTitle
 */

function getMatchedTitle($pushConfig) {
    if (isset($pushConfig['enableGoodsTitle']) && $pushConfig['enableGoodsTitle'] && !empty($pushConfig['goodsTitleRules'])) {
        $rules = $pushConfig['goodsTitleRules'];
        usort($rules, function($a, $b) {
            return (isset($a['priority']) ? $a['priority'] : 99) - (isset($b['priority']) ? $b['priority'] : 99);
        });
        
        $matchedTitle = '';
        foreach ($rules as $rule) {
            if (isset($rule['status']) && (int)$rule['status'] === 1) {
                 $matchedTitle = $rule['title'];
                 break;
            }
        }
        return $matchedTitle;
    }
    return 'Default';
}

// Test Case 1: Multiple enabled rules, priority sort
$config1 = [
    'enableGoodsTitle' => true,
    'goodsTitleRules' => [
        ['title' => 'Low Priority', 'priority' => 50, 'status' => 1],
        ['title' => 'High Priority', 'priority' => 10, 'status' => 1],
        ['title' => 'Disabled High', 'priority' => 5, 'status' => 0]
    ]
];
$res1 = getMatchedTitle($config1);
echo "Test 1 (Expected 'High Priority'): " . $res1 . "\n";

// Test Case 2: Only disabled rules
$config2 = [
    'enableGoodsTitle' => true,
    'goodsTitleRules' => [
        ['title' => 'Disabled', 'priority' => 1, 'status' => 0]
    ]
];
$res2 = getMatchedTitle($config2);
echo "Test 2 (Expected ''): '" . $res2 . "'\n";

// Test Case 3: Switch off
$config3 = [
    'enableGoodsTitle' => false,
    'goodsTitleRules' => [
        ['title' => 'Ignored', 'priority' => 1, 'status' => 1]
    ]
];
$res3 = getMatchedTitle($config3);
echo "Test 3 (Expected 'Default'): " . $res3 . "\n";
