<?php
namespace app\common\library\Jdl;

/**
 * 京东物流配置类
 * 管理API端点、域名、路径等配置信息
 */
class JdlConfig
{
    /**
     * 生产环境API地址
     */
    const PROD_API_URL = 'https://api.jdl.com';
    
    /**
     * 预发环境API地址
     */
    const UAT_API_URL = 'https://uat-api.jdl.com';
    
    /**
     * API路径配置
     */
    const API_PATHS = [
        'createOrder' => '/ecap/v1/orders/create',           // 创建订单
        'queryTrace' => '/ecap/v1/orders/trace/query',       // 查询轨迹
        'queryStatus' => '/ecap/v1/orders/status/get',       // 查询订单状态
        'getPrintData' => '/PullDataService/pullData',       // 获取打印数据
        'getTemplates' => '/cloud/print/getTemplates',       // 获取打印模板列表
    ];
    
    /**
     * Domain配置（对接方案编码）
     */
    const DOMAINS = [
        'createOrder' => 'jdecap',          // 订单服务
        'queryTrace' => 'jdecap',           // 轨迹查询
        'queryStatus' => 'jdecap',          // 状态查询
        'getPrintData' => 'jdcloudprint',   // 云打印
        'getTemplates' => 'jdcloudprint',   // 模板服务
    ];
    
    /**
     * 承运商编码
     */
    const CP_CODES = [
        'JD' => 'JD',       // 京东快递
        'JDKY' => 'JDKY',   // 京东快运
        'JDDJ' => 'JDDJ',   // 京东大件
        'ZY' => 'ZY',       // 众邮
    ];
    
    /**
     * 标准打印模板编码
     */
    const TEMPLATE_CODES = [
        'JD' => 'jdkd76x130',      // 京东快递标准模板76x130
        'JDKY' => 'jdky76x130',    // 京东快运标准模板76x130
        'JDDJ' => 'jddj76x130',    // 京东大件标准模板76x130
    ];
    
    /**
     * 订单来源类型
     */
    const ORDER_ORIGIN = [
        'C2C' => 0,  // 现结
        'B2C' => 1,  // 月结
        'C2B' => 2,  // 取件
    ];
    
    /**
     * 获取API基础URL
     * @param array $config 配置数组
     * @return string
     */
    public static function getBaseUrl($config)
    {
        // 检查是否为沙箱环境
        if (isset($config['api_url']) && strpos($config['api_url'], 'uat') !== false) {
            return self::UAT_API_URL;
        }
        return self::PROD_API_URL;
    }
    
    /**
     * 获取API路径
     * @param string $action 操作名称
     * @return string|null
     */
    public static function getApiPath($action)
    {
        return isset(self::API_PATHS[$action]) ? self::API_PATHS[$action] : null;
    }
    
    /**
     * 获取Domain
     * @param string $action 操作名称
     * @return string|null
     */
    public static function getDomain($action)
    {
        return isset(self::DOMAINS[$action]) ? self::DOMAINS[$action] : null;
    }
    
    /**
     * 获取模板URL
     * @param string $templateCode 模板编码
     * @return string
     */
    public static function getTemplateUrl($templateCode)
    {
        return 'https://template-content.jd.com/template-oss?tempCode=' . $templateCode;
    }
    
    /**
     * 获取默认模板编码
     * @param string $cpCode 承运商编码
     * @return string
     */
    public static function getDefaultTemplateCode($cpCode)
    {
        return isset(self::TEMPLATE_CODES[$cpCode]) ? self::TEMPLATE_CODES[$cpCode] : 'jdkd76x130';
    }
}
