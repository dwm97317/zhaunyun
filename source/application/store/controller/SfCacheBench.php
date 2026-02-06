<?php
namespace app\store\controller;

use think\Controller;
use think\Cache;
use app\common\service\SfCache;
use app\store\model\Inpack;

/**
 * 缓存性能基准测试控制器
 */
class SfCacheBench extends Controller
{
    /**
     * 基准测试：对比缓存前后性能
     */
    public function benchmark()
    {
        $orderId = input('order_id', 69463);
        $iterations = input('iterations', 10);
        
        $order = Inpack::detail($orderId);
        if (!$order) {
            return json(['error' => '订单不存在']);
        }
        
        $waybillNo = $order['t_order_sn'] ?? $order['order_sn'] ?? '';
        
        // 清空缓存
        Cache::clear();
        SfCache::resetStats();
        
        $results = [
            'cold_start' => [],
            'warm_cache' => [],
            'stats' => []
        ];
        
        // 1. 冷启动测试（无缓存）
        $coldTimes = [];
        for ($i = 0; $i < 3; $i++) {
            Cache::clear();
            $start = microtime(true);
            $this->request->get(['order_id' => $orderId]);
            $response = $this->callGetPrintConfig($orderId);
            $elapsed = (microtime(true) - $start) * 1000;
            $coldTimes[] = round($elapsed, 2);
            sleep(1);
        }
        
        $results['cold_start'] = [
            'times' => $coldTimes,
            'avg' => round(array_sum($coldTimes) / count($coldTimes), 2),
            'min' => min($coldTimes),
            'max' => max($coldTimes)
        ];
        
        // 2. 热缓存测试
        $warmTimes = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $response = $this->callGetPrintConfig($orderId);
            $elapsed = (microtime(true) - $start) * 1000;
            $warmTimes[] = round($elapsed, 2);
        }
        
        $results['warm_cache'] = [
            'times' => $warmTimes,
            'avg' => round(array_sum($warmTimes) / count($warmTimes), 2),
            'min' => min($warmTimes),
            'max' => max($warmTimes),
            'p50' => $this->percentile($warmTimes, 50),
            'p95' => $this->percentile($warmTimes, 95),
            'p99' => $this->percentile($warmTimes, 99)
        ];
        
        // 3. 性能提升
        $improvement = round((1 - $results['warm_cache']['avg'] / $results['cold_start']['avg']) * 100, 1);
        
        $results['improvement'] = [
            'percentage' => $improvement,
            'speedup' => round($results['cold_start']['avg'] / $results['warm_cache']['avg'], 2) . 'x',
            'time_saved_ms' => round($results['cold_start']['avg'] - $results['warm_cache']['avg'], 2)
        ];
        
        // 4. 缓存统计
        $results['stats'] = SfCache::getStats();
        
        // 5. SLA 达成情况
        $results['sla'] = [
            'target_ms' => 500,
            'achieved' => $results['warm_cache']['avg'] < 500,
            'p95_achieved' => $results['warm_cache']['p95'] < 500,
            'p99_achieved' => $results['warm_cache']['p99'] < 500
        ];
        
        return json($results);
    }
    
    /**
     * 并发测试
     */
    public function concurrent()
    {
        $orderId = input('order_id', 69463);
        $concurrency = input('concurrency', 10);
        
        Cache::clear();
        SfCache::resetStats();
        
        $results = [];
        $start = microtime(true);
        
        // 模拟并发请求
        for ($i = 0; $i < $concurrency; $i++) {
            $reqStart = microtime(true);
            $response = $this->callGetPrintConfig($orderId);
            $elapsed = (microtime(true) - $reqStart) * 1000;
            $results[] = round($elapsed, 2);
        }
        
        $totalTime = (microtime(true) - $start) * 1000;
        
        return json([
            'concurrency' => $concurrency,
            'total_time_ms' => round($totalTime, 2),
            'avg_time_ms' => round(array_sum($results) / count($results), 2),
            'min_ms' => min($results),
            'max_ms' => max($results),
            'throughput_rps' => round($concurrency / ($totalTime / 1000), 2),
            'cache_stats' => SfCache::getStats(),
            'all_times' => $results
        ]);
    }
    
    /**
     * 缓存命中率测试
     */
    public function hitRate()
    {
        $orderIds = input('order_ids', '69463,69463,69463,69463,69463');
        $ids = explode(',', $orderIds);
        
        Cache::clear();
        SfCache::resetStats();
        
        $results = [];
        
        foreach ($ids as $orderId) {
            $start = microtime(true);
            $response = $this->callGetPrintConfig(trim($orderId));
            $elapsed = (microtime(true) - $start) * 1000;
            $results[] = [
                'order_id' => trim($orderId),
                'time_ms' => round($elapsed, 2)
            ];
        }
        
        return json([
            'requests' => $results,
            'cache_stats' => SfCache::getStats()
        ]);
    }
    
    /**
     * 压力测试
     */
    public function stress()
    {
        $duration = input('duration', 10); // 秒
        $orderId = input('order_id', 69463);
        
        Cache::clear();
        SfCache::resetStats();
        
        $startTime = time();
        $endTime = $startTime + $duration;
        $count = 0;
        $times = [];
        $errors = 0;
        
        while (time() < $endTime) {
            try {
                $start = microtime(true);
                $response = $this->callGetPrintConfig($orderId);
                $elapsed = (microtime(true) - $start) * 1000;
                $times[] = round($elapsed, 2);
                $count++;
            } catch (\Exception $e) {
                $errors++;
            }
        }
        
        $actualDuration = time() - $startTime;
        
        return json([
            'duration_seconds' => $actualDuration,
            'total_requests' => $count,
            'errors' => $errors,
            'rps' => round($count / $actualDuration, 2),
            'avg_time_ms' => round(array_sum($times) / count($times), 2),
            'min_ms' => min($times),
            'max_ms' => max($times),
            'p50' => $this->percentile($times, 50),
            'p95' => $this->percentile($times, 95),
            'p99' => $this->percentile($times, 99),
            'cache_stats' => SfCache::getStats()
        ]);
    }
    
    /**
     * 调用 getPrintConfig
     */
    private function callGetPrintConfig($orderId)
    {
        $url = "http://localhost:8080/index.php?s=/store/sf_print/getPrintConfig&order_id={$orderId}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * 计算百分位数
     */
    private function percentile($array, $percentile)
    {
        sort($array);
        $index = ceil(count($array) * $percentile / 100) - 1;
        return $array[$index];
    }
}
