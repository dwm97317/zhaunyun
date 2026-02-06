<?php

namespace app\common\library\zto;

/**
 * 中通快递配置类
 * 负责管理 API 地址、默认值、云打印配置等
 * 
 * 功能列表：
 * 1. API 地址管理（轨迹查询、创建订单、云打印）
 * 2. 默认值管理（发件人、收件人信息）
 * 3. 云打印配置管理（打印机、增值服务、回单）
 * 4. 配置验证
 * 
 * @method static string getApiUrl(array $config, string $type) 获取API地址
 * @method static mixed getDefault(string $key, mixed $default) 获取默认值
 * @method static mixed get(array $config, string $key, mixed $default) 获取配置值
 * @method static array getPrinterConfig(array $config) 获取打印机配置
 * @method static array buildCloudPrintParams(array $waybillNos, array $printerConfig) 构建云打印请求参数
 * @method static array validatePrinterConfig(array $printerConfig) 验证打印机配置
 * @method static string getAppreciationTypeName(int $type) 获取增值服务类型名称
 */
class ZtoConfig
{
    /**
     * 中通标准 API 基础地址
     */
    const API_BASE_URL = 'https://japi.zto.com';
    
    /**
     * 中通测试环境 API 地址
     */
    const API_TEST_URL = 'https://japi-test.zto.com';
    
    /**
     * 中通轨迹查询 API 地址
     */
    const TRACK_API_URL = 'https://api.zto.com';

    /**
     * API 路径配置
     */
    const PATHS = [
        'track' => '/zto.merchant.waybill.track.query',      // 轨迹查询
        'createOrder' => '/zto.open.createOrder',            // 创建订单（标准中通）
        'managerOrder' => '/zto.ehk.receiveOpenOrder',       // 创建订单（中通管家）
        'cloudPrint' => '/zto.print.batchCloudPrint',        // 批量云打印
        'bagAddrMark' => '/zto.innovate.bagAddrMark',        // 大头笔查询
    ];

    /**
     * 默认配置值
     */
    const DEFAULTS = [
        'sender_name' => '发件人',
        'sender_mobile' => '13800138000',
        'sender_province' => '上海',
        'sender_city' => '上海市',
        'sender_district' => '青浦区',
        'sender_address' => '详细地址',
        'sender_postcode' => '000000',
        
        'receiver_name' => '收件人',
        'receiver_mobile' => '13900139000',
        'receiver_province' => '上海',
        'receiver_city' => '上海市',
        'receiver_district' => '闵行区',
        'receiver_address' => '详细地址',
        'receiver_postcode' => '000000',
        
        'account_password' => 'ZTO123',  // 测试环境默认密码
        'print_channel' => 'ZOP',        // 打印渠道
    ];

    /**
     * 云打印配置字段映射
     * 用于从 push_config_json 中提取打印机配置
     */
    const PRINTER_CONFIG_FIELDS = [
        'printerId' => '',           // 打印机名称（PC端客户端打印时必填）
        'deviceId' => '',            // 设备ID（与二维码ID二选一）
        'qrcodeId' => '',            // 二维码ID（与设备ID二选一）
        'printChannel' => 'ZOP',     // 打印渠道（固定值）
        'appreciationEnabled' => false,  // 是否启用增值服务
        'appreciationDTOS' => [],    // 增值服务列表
        'backBillEnabled' => false,  // 是否启用回单
        'backBillCode' => '',        // 回单编号
    ];

    /**
     * 增值服务类型映射
     * 用于云打印接口的增值服务配置
     */
    const APPRECIATION_TYPES = [
        1 => '到付',
        2 => '代收货款',
        6 => '中通标快',
        16 => '隐私服务',
        18 => '保价',
        29 => '中通好快',
    ];

    /**
     * 获取 API URL
     * @param array $config 渠道配置
     * @param string $type API 类型：track, createOrder, managerOrder
     * @return string 完整的 API URL
     */
    public static function getApiUrl(array $config, $type = 'createOrder')
    {
        $baseUrl = isset($config['apiurl']) && $config['apiurl'] !== ''
            ? rtrim($config['apiurl'], '/')
            : self::API_BASE_URL;

        $path = self::PATHS[$type] ?? '';
        
        // 如果 baseUrl 已经包含了路径，直接返回
        if (strpos($baseUrl, $path) !== false) {
            return $baseUrl;
        }
        
        return $baseUrl . $path;
    }

    /**
     * 获取默认值
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getDefault($key, $default = null)
    {
        return self::DEFAULTS[$key] ?? $default;
    }

    /**
     * 获取配置值（带默认值）
     * @param array $config 配置数组
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(array $config, $key, $default = null)
    {
        return isset($config[$key]) ? $config[$key] : ($default ?? self::getDefault($key));
    }

    /**
     * 获取打印机配置
     * 从 push_config_json 中提取云打印相关配置
     * 
     * @param array $config 渠道配置数组
     * @return array 打印机配置数组
     * 
     * 使用示例：
     * ```php
     * $printerConfig = ZtoConfig::getPrinterConfig($ditchConfig);
     * // 返回：
     * [
     *     'printerId' => '打印机名称',
     *     'deviceId' => '8CEEC48B18D0:52',
     *     'qrcodeId' => 'epe338c5e',
     *     'printChannel' => 'ZOP',
     *     'appreciationEnabled' => true,
     *     'appreciationDTOS' => [
     *         ['type' => 18, 'amount' => 100.00],  // 保价
     *         ['type' => 16, 'amount' => 0],       // 隐私服务
     *     ],
     *     'backBillEnabled' => false,
     *     'backBillCode' => '',
     * ]
     * ```
     */
    public static function getPrinterConfig(array $config)
    {
        $printerConfig = self::PRINTER_CONFIG_FIELDS;
        
        // 从 push_config_json 中提取配置
        if (isset($config['push_config_json']) && !empty($config['push_config_json'])) {
            $pushConfig = is_string($config['push_config_json']) 
                ? json_decode($config['push_config_json'], true) 
                : $config['push_config_json'];
            
            if (isset($pushConfig['ztoPrinterConfig']) && is_array($pushConfig['ztoPrinterConfig'])) {
                $printerConfig = array_merge($printerConfig, $pushConfig['ztoPrinterConfig']);
            }
        }
        
        return $printerConfig;
    }

    /**
     * 构建云打印请求参数
     * 
     * @param array $waybillNos 运单号数组
     * @param array $printerConfig 打印机配置
     * @return array 云打印请求参数
     * 
     * 使用示例：
     * ```php
     * $params = ZtoConfig::buildCloudPrintParams(
     *     ['ZTO123456', 'ZTO123457'],
     *     $printerConfig
     * );
     * ```
     */
    public static function buildCloudPrintParams(array $waybillNos, array $printerConfig)
    {
        $params = [
            'billCodes' => implode(',', $waybillNos),  // 运单号，多个用逗号分隔
            'printChannel' => isset($printerConfig['printChannel']) ? $printerConfig['printChannel'] : 'ZOP',
        ];

        // PC端客户端打印时必填
        if (!empty($printerConfig['printerId'])) {
            $params['printerId'] = $printerConfig['printerId'];
        }

        // 设备ID或二维码ID二选一
        if (!empty($printerConfig['deviceId'])) {
            $params['deviceId'] = $printerConfig['deviceId'];
        } elseif (!empty($printerConfig['qrcodeId'])) {
            $params['qrcodeId'] = $printerConfig['qrcodeId'];
        }

        // 增值服务配置
        if (!empty($printerConfig['appreciationEnabled']) && !empty($printerConfig['appreciationDTOS'])) {
            $params['appreciationDTOS'] = $printerConfig['appreciationDTOS'];
        }

        // 回单配置
        if (!empty($printerConfig['backBillEnabled']) && !empty($printerConfig['backBillCode'])) {
            $params['backBillCode'] = $printerConfig['backBillCode'];
        }

        return $params;
    }

    /**
     * 验证打印机配置
     * 
     * @param array $printerConfig 打印机配置
     * @return array ['valid' => bool, 'errors' => array]
     * 
     * 验证规则：
     * 1. deviceId 或 qrcodeId 必须填写其一
     * 2. printChannel 必须为 'ZOP'
     * 3. 如果启用增值服务，必须配置 appreciationDTOS
     * 4. 如果启用回单，必须配置 backBillCode
     */
    public static function validatePrinterConfig(array $printerConfig)
    {
        $errors = [];

        // 验证设备ID或二维码ID
        if (empty($printerConfig['deviceId']) && empty($printerConfig['qrcodeId'])) {
            $errors[] = '设备ID或二维码ID必须填写其一';
        }

        // 验证打印渠道
        if (empty($printerConfig['printChannel']) || $printerConfig['printChannel'] !== 'ZOP') {
            $errors[] = '打印渠道必须为 ZOP';
        }

        // 验证增值服务配置
        if (!empty($printerConfig['appreciationEnabled'])) {
            if (empty($printerConfig['appreciationDTOS']) || !is_array($printerConfig['appreciationDTOS'])) {
                $errors[] = '启用增值服务时必须配置增值服务列表';
            }
        }

        // 验证回单配置
        if (!empty($printerConfig['backBillEnabled'])) {
            if (empty($printerConfig['backBillCode'])) {
                $errors[] = '启用回单时必须配置回单编号';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * 获取增值服务类型名称
     * 
     * @param int $type 增值服务类型代码
     * @return string 类型名称
     */
    public static function getAppreciationTypeName($type)
    {
        return isset(self::APPRECIATION_TYPES[$type]) ? self::APPRECIATION_TYPES[$type] : '未知';
    }
}
