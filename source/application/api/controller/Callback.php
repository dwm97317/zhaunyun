<?php

namespace app\api\controller;

use app\api\controller\Controller;
use app\common\service\trace\SfService;

/**
 * 回调控制器
 */
class Callback extends Controller
{
    /**
     * 重写初始化方法，跳过基类的 wxapp_id 验证
     */
    public function _initialize()
    {
        // 覆盖父类方法，不做任何操作
    }

    /**
     * SF 路由推送接口
     * URL: /api/callback/sf_route
     */
    public function sf_route()
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        // 兼容顺丰可能使用 form-data 或其他方式传 JSON
        if (empty($data)) {
            $post = input('post.');
            // 某些情况下顺丰会把JSON放在某个字段里? 暂时假设直接POST或者RawBody
            if (!empty($post)) {
                $data = $post;
            }
        }

        if (empty($data)) {
             return json(['head' => 'ERR', 'message' => 'Empty Body']);
        }

        // 记录原始请求日志
        $logPath = '../runtime/log/sf_route_dump.log';
        $logContent = date('Y-m-d H:i:s') . " [SF_CALLBACK] \n" . $input . "\n--------------------\n";
        file_put_contents($logPath, $logContent, FILE_APPEND);

        $res = SfService::handleRouteCallback($data);
        return json($res);
    }

    /**
     * JD 路由推送接口 (数据捕获阶段)
     * URL: /api/callback/jd_trace
     */
    public function jd_trace()
    {
        // 1. 获取原始数据
        $input = file_get_contents("php://input");
        // 3. 记录日志 (可选，保留以便排查)
        $logPath = '../runtime/log/jd_trace_dump.log';
        $logContent = date('Y-m-d H:i:s') . " [JD_CALLBACK] \n" . $input . "\n--------------------\n";
        file_put_contents($logPath, $logContent, FILE_APPEND);
        $data = json_decode($input, true);

        // 2. 也是为了兼容，尝试获取POST
        if (empty($data)) {
            $post = input('post.');
            if (!empty($post)) {
                 $data = $post;
            }
        }

        if (empty($data)) {
            return json(['code' => 400, 'message' => 'Empty Body']);
        }

        // 4. 业务处理
        $res = \app\common\service\trace\JdService::handleCallback($data);

        return json($res);
    }

    /**
     * 测试 JD 下单
     * URL: /api/callback/test_create
     */
    public function test_create()
    {
        $config = [
            'app_key'       => '9e941527571e4bf2bad8ca6e14ae8bc0',
            'app_secret'    => 'd73d7fb69cfb49d2a2a550b25a794b6b',
            'access_token'  => '6dec8ad7165541dab0e9dbe5dcc6ca20',
            'api_url'       => 'https://uat-api.jdl.com/ecap/v1/orders/create',
            'customer_code' => '020K10143566'
        ];

        $jd = new \app\common\library\Ditch\Jd($config);

        $orderId = 'TEST_WEB_' . date('YmdHis') . '_' . rand(100, 999);
        $data = [
            'order_sn'        => $orderId,
            'product_code'    => 'ed-m-0001',
            'sender_name'     => '李先生',
            'sender_mobile'   => '13188888888',
            'sender_address'  => '上海市青浦区香花桥街道崧复路xxx号',
            'sender_province' => '上海',
            'sender_city'     => '上海市',
            'sender_district' => '青浦区',
            
            'name'      => '陈先生',
            'phone'     => '13188888888',
            'detail'    => '江苏省无锡市江阴市新桥镇北欧印象xx栋xxx号',
            'province'  => '江苏省',
            'city'      => '无锡市',
            'region'    => '江阴市',
            
            'quantity' => 2,
            'weight'   => 2.5
        ];

        try {
            $result = $jd->createOrder($data);
            return json([
                'msg' => 'Test Finished',
                'orderId' => $orderId,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return json(['error' => $e->getMessage()]);
        }
    }

    /**
     * ZTO 路由推送接口
     * URL: /api/callback/zto_trace
     */
    public function zto_trace()
    {
        // 1. 获取原始数据
        $input = file_get_contents("php://input");
        // 使用 $_POST 获取原生数据，避免 input() 默认的 htmlspecialchars 过滤导致 JSON 解析失败
        $post = $_POST;
        
        // 记录日志
        $logPath = '../runtime/log/zto_trace_dump.log';
        $logContent = date('Y-m-d H:i:s') . " [ZTO_CALLBACK] \nINPUT: " . $input . "\nPOST: " . json_encode($post, JSON_UNESCAPED_UNICODE) . "\n--------------------\n";
        file_put_contents($logPath, $logContent, FILE_APPEND);

        $data = [];
        // ZTO 通常是 x-www-form-urlencoded, 参数在 $_POST 中
        if (!empty($post)) {
            $data = $post;
        } elseif (!empty($input)) {
            // 尝试解析 JSON body
            $json = json_decode($input, true);
            if (!empty($json)) {
                $data = $json;
            } else {
                 // 有时 ZTO 会把 JSON 放在 data 参数里提交，但 Content-Type 是 application/x-www-form-urlencoded
                 // 如果 input 是 key=value 格式，parse_str
                 parse_str($input, $parsed);
                 if (!empty($parsed)) {
                     $data = $parsed;
                 }
            }
        }
        
        if (empty($data)) {
            return json(['result' => 'false', 'message' => 'Empty Request', 'status' => false]);
        }

        // 4. 业务处理
        $res = \app\common\service\trace\ZtoService::handleCallback($data);

        return json($res);
    }

}
