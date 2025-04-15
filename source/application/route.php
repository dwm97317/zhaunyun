<?php

// 设限制URL兼容模式
\think\Url::root('index.php?s=');
// 微信回调路由
// Route::post('api/wechat/callback', 'api/Wechat/callback');
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];

