<?php
// 17 track 物流查询接口 v2.4
namespace app\api\service\trackApi;  
use app\common\model\Setting;

Class TrackApi {

      public $api = 'https://api.17track.net/track/v2.4'; // API 请求地址
      public $secret = '5A0B5AEF8526C22944C92ADCC50C13CA'; // 秘钥
      public $lang = 'en';
      public $wxappid = '';
      

      // 封装header 头部数据
      private function header(){
          $setting = Setting::getItem("store",$this->wxappid);

          if ($setting['track17']['key']){
                $this->secret = $setting['track17']['key'];
          }
          return [
             '17token:'.$this->secret,
             'Content-Type:application/json'
          ];
      }
      
      private function carrierIdentify($body){
         $api = '/carrierIdentify';
         $data = json_encode($body);
         $res = $this->curl_post($api,$data);
   
         if (!$res['code']){
              echo "请求出错";
              die;
          }
          $resJson = json_decode($res['data'],true);
        
          if(isset($resJson['data']['accepted'])){
              return $resJson['data']['accepted'][0]['carrier'];
          }
          return null;
      }
      
      public function register($data){
          $this->wxappid = $data['wxapp_id'] ;  
          $setting = Setting::getItem("store",$this->wxappid);
          
          // 构建请求体，v2.4 API 格式
          $body = [
              'number' => $data['track_sn'],
          ];
          
          // 运输商代码（可选，但建议提供）
          if (!empty($data['t_number'])) {
              $body['carrier'] = $data['t_number'];
          }
          
          // 订单时间格式改为 YYYY/MM/DD
          if (isset($data['order_time'])) {
              $body['order_time'] = date("Y/m/d", strtotime($data['order_time']));
          } else {
              $body['order_time'] = date("Y/m/d");
          }
          
          // 翻译语言（如果有配置）
          if (!empty($setting['track17']['lang'])) {
              $body['lang'] = $setting['track17']['lang'];
          }
          
          // 手机号处理：v2.4 API 使用 phone_number 或 phone_number_last_4
          if (!empty($data['phone'])) {
              $phone = $data['phone'];
              // 如果手机号是4位数字，使用 phone_number_last_4
              if (strlen($phone) == 4 && is_numeric($phone)) {
                  $body['phone_number_last_4'] = $phone;
              } else {
                  // 完整手机号使用 phone_number
                  $body['phone_number'] = $phone;
              }
          }
          
          // 特殊处理：t_number == 100003 的情况
          // 如果这个运输商需要特殊参数，可以在这里添加
          if($data['t_number'] == 100003){
              // 100003 可能需要特殊跟踪信息，根据实际情况调整
              // 例如可能需要 ship_date 或其他参数
              if (isset($data['ship_date'])) {
                  $body['ship_date'] = date("Y/m/d", strtotime($data['ship_date']));
              } else {
                  $body['ship_date'] = date("Y/m/d");
              }
          }
          
          // 其他可选参数
          if (isset($data['destination_country'])) {
              $body['destination_country'] = $data['destination_country'];
          }
          if (isset($data['destination_city'])) {
              $body['destination_city'] = $data['destination_city'];
          }
          if (isset($data['destination_postal_code'])) {
              $body['destination_postal_code'] = $data['destination_postal_code'];
          }
          if (isset($data['origin_country'])) {
              $body['origin_country'] = $data['origin_country'];
          }
          if (isset($data['email'])) {
              $body['email'] = $data['email'];
          }
          if (isset($data['order_no'])) {
              $body['order_no'] = $data['order_no'];
          }
          if (isset($data['tag'])) {
              $body['tag'] = $data['tag'];
          }
          if (isset($data['remark'])) {
              $body['remark'] = $data['remark'];
          }
          
          $api = '/register';
          // v2.4 API 请求体是数组格式
          $requestBody = [$body];
          $requestData = json_encode($requestBody);
            
          $res = $this->curl_post($api, $requestData);
        //   dump($res);die;
          if (!$res['code']){
              echo "请求出错";
              die;
          }
          $resJson = json_decode($res['data'], true);
         
          // v2.4 API: code 为 0 表示成功
          if ($resJson['code'] != 0) {
              return false;
          }
          
          // 检查 accepted 和 rejected 数组
          $accepted = isset($resJson['data']['accepted']) ? $resJson['data']['accepted'] : [];
          $rejected = isset($resJson['data']['rejected']) ? $resJson['data']['rejected'] : [];
          
          // 如果有 accepted 数据，说明至少有一个单号注册成功
          if (!empty($accepted)) {
              return true;
          }
          
          // 检查 rejected 数组中的错误
          $bool = true;
          foreach ($rejected as $v){
              if (isset($v['error'])){
                  $err = $v['error'];
                  $errorCode = $err['code'];
                  // -18019901 表示单号已存在，不算错误（注册成功）
                  // 其他错误代码表示注册失败
                  if ($errorCode != -18019901){
                      $bool = false;
                      // 记录具体错误信息（可选，用于调试）
                      // -18019911: 运输商暂时不支持注册
                      // -18019902: 单号格式错误或其他问题
                  }
              }     
          }
         
          return $bool;
      }
      
      // 获取 物流轨迹
      /**
       * 传单号
       * @param array $data 包含 track_sn(物流单号), t_number(运输商代码), wxapp_id(小程序ID)
       * @return array 返回 track_info 数据结构
       */
      public function track($data){
          $this->wxappid = $data['wxapp_id'] ; 
          $api = '/gettrackinfo';
          
          // v2.4 API 格式：构建请求体
          $body = [
              'number' => $data['track_sn']
          ];
          
          // 如果提供了运输商代码，添加到请求中（可选，但建议提供以提高查询准确性）
          if (!empty($data['t_number'])) {
              $body['carrier'] = $data['t_number'];
          }
          
          // v2.4 API 请求体是数组格式，每次最多可提交 40 个物流单号
          $requestBody = [$body];
          $requestData = json_encode($requestBody);
        //   dump($data);die;
          $res = $this->curl_post($api, $requestData);
          if (!$res['code']){
              echo "请求出错";
              die;
          }
                //  dump($res);die;
          $resJson = json_decode($res['data'], true);
         
          // v2.4 API: code 为 0 表示成功，非 0 表示有错误
          if($resJson['code'] != 0){
              // 401 表示未授权
              if($resJson['code'] == 401){
                  return []; 
              }
              // 其他错误
              return [];
          }
          
          // 检查是否有 accepted 数据
          $accepted = isset($resJson['data']['accepted']) ? $resJson['data']['accepted'] : [];
          $rejected = isset($resJson['data']['rejected']) ? $resJson['data']['rejected'] : [];
          
          // 检查 rejected 数组（拒绝的单号）
          $shouldUseRealtime = false;
          if (!empty($rejected)){
              foreach ($rejected as $rejectedItem) {
                  if (isset($rejectedItem['error']['code'])){
                      $errorCode = $rejectedItem['error']['code'];
                      // -18019902 表示单号未注册，使用实时查询接口（实时查询接口不需要先注册）
                      // -18019911 表示运输商暂时不支持注册/查询，尝试使用实时查询接口
                      if ($errorCode == -18019902 || $errorCode == -18019911){
                          $shouldUseRealtime = true;
                          break;
                      }
                  }
              }
          }
          
          // 如果遇到 -18019902 或 -18019911 错误，使用实时查询接口重试
          if ($shouldUseRealtime) {
              return $this->trackRealTime($data);
          }
          
          // 如果有 accepted 数据，返回第一个
          if (!empty($accepted)) {
              // 返回第一个接受的数据（通常只有一个）
              return $this->renderTrackList($accepted[0]);
          }
          
          // 如果 accepted 为空，即使没有 rejected 错误，也返回空数组
          return [];
      }
      
      // 实时查询物流轨迹（当运输商不支持注册时使用）
      /**
       * 使用实时查询接口获取物流轨迹
       * @param array $data 包含 track_sn(物流单号), t_number(运输商代码), wxapp_id(小程序ID), cacheLevel(可选，0=标准模式，1=即时模式)
       * @return array 返回 track_info 数据结构
       */
      public function trackRealTime($data){
          $this->wxappid = $data['wxapp_id'] ; 
          $api = '/getRealTimeTrackInfo';
          
          // 构建请求体
          $body = [
              'number' => $data['track_sn']
          ];
          
          // 如果提供了运输商代码，添加到请求中
          if (!empty($data['t_number'])) {
              $body['carrier'] = $data['t_number'];
          }
          
          // cacheLevel: 0=标准模式（默认，扣1个额度），1=即时模式（扣10个额度）
          // 默认使用标准模式，如果用户指定了 cacheLevel 则使用用户指定的值
          $cacheLevel = isset($data['cacheLevel']) ? $data['cacheLevel'] : 0;
          $body['cacheLevel'] = $cacheLevel;
          
          // 可选参数：支持更多的附加跟踪参数以提高查询准确性
          if (isset($data['origin_country'])) {
              $body['origin_country'] = $data['origin_country'];
          }
          if (isset($data['ship_date'])) {
              $body['ship_date'] = date("Y/m/d", strtotime($data['ship_date']));
          }
          if (isset($data['destination_postal_code'])) {
              $body['destination_postal_code'] = $data['destination_postal_code'];
          }
          if (isset($data['destination_country'])) {
              $body['destination_country'] = $data['destination_country'];
          }
          if (isset($data['destination_city'])) {
              $body['destination_city'] = $data['destination_city'];
          }
          if (isset($data['shipper'])) {
              $body['shipper'] = $data['shipper'];
          }
          if (isset($data['consignee'])) {
              $body['consignee'] = $data['consignee'];
          }
          if (!empty($data['phone'])) {
              $phone = $data['phone'];
              if (strlen($phone) == 4 && is_numeric($phone)) {
                  $body['phone_number_last_4'] = $phone;
              } else {
                  $body['phone_number'] = $phone;
              }
          }
          if (isset($data['final_carrier'])) {
              $body['final_carrier'] = $data['final_carrier'];
          }
          
          // 实时查询接口每次请求限制一个单号
          $requestBody = [$body];
          $requestData = json_encode($requestBody);
          
          $res = $this->curl_post($api, $requestData);
          if (!$res['code']){
              echo "请求出错";
              die;
          }
          $resJson = json_decode($res['data'], true);
        //  dump($res);die;
          // v2.4 API: code 为 0 表示成功，非 0 表示有错误
          if($resJson['code'] != 0){
              // 401 表示未授权
              if($resJson['code'] == 401){
                  return []; 
              }
              // 其他错误
              return [];
          }
          
          // 检查是否有 accepted 数据
          $accepted = isset($resJson['data']['accepted']) ? $resJson['data']['accepted'] : [];
          $rejected = isset($resJson['data']['rejected']) ? $resJson['data']['rejected'] : [];
          
          // 检查 rejected 数组
          if (!empty($rejected)){
              foreach ($rejected as $rejectedItem) {
                  if (isset($rejectedItem['error']['code'])){
                      $errorCode = $rejectedItem['error']['code'];
                      // -18019903 表示系统无法识别物流单号所属运输商
                      // 其他错误代码表示查询失败
                      if ($errorCode == -18019903){
                          return [];
                      }
                  }
              }
          }
          
          // 如果有 accepted 数据，返回第一个
          if (!empty($accepted)) {
              return $this->renderTrackList($accepted[0]);
          }
          
          return [];
      }
      
      // 渲染轨迹列表信息
      /**
       * 处理 v2.4 API 返回的物流轨迹数据
       * @param array $data accepted 数组中的单个数据项，包含 number, carrier, track_info 等
       * @return array 返回 track_info 数据结构，供调用方使用
       */
      public function renderTrackList($data){
         // v2.4 API 返回的数据结构：
         // {
         //   "number": "...",
         //   "carrier": 100003,
         //   "track_info": {
         //     "tracking": {
         //       "providers": [...]
         //     },
         //     ...
         //   }
         // }
         
         // 检查 track_info 是否存在
         if (!isset($data['track_info']) || empty($data['track_info'])) {
             return [];
         }
         
         $trackInfo = $data['track_info'];
         
         // 确保 tracking.providers 结构存在
         if (!isset($trackInfo['tracking']['providers'])) {
             // 如果没有 providers，初始化为空数组
             $trackInfo['tracking']['providers'] = [];
         }
         
         // 为了兼容性，保留原有的数据结构
         // 调用方期望使用 $track['tracking']['providers'] 来遍历事件
         // 所以直接返回 track_info 即可
         
         // 可选：添加一些额外的处理逻辑
         // 例如：格式化时间、翻译描述等
         
         return $trackInfo;
      }

      public function curl_post($url,$data,$header=[],$timeout=30){
        
          $_curl = curl_init();
          $url = $this->api.$url;
          curl_setopt($_curl, CURLOPT_URL, $url);
          if (!empty($data)) {
              curl_setopt($_curl, CURLOPT_POSTFIELDS, $data);
              curl_setopt($_curl, CURLOPT_POST, true);
          }
          if(substr($url,0,5) == 'https'){
            curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, 2);
          }
          $header_common = $this->header();
          if ($header){
              $header_common = array_merge($header_common,$header);
          }
          curl_setopt($_curl, CURLOPT_HTTPHEADER, $header_common);
          curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
          $result = curl_exec($_curl);
          if (curl_errno($_curl)){
              return ['code'=>0,'msg'=>'请求出错'];
          }
          return ['code'=>1,'data'=>$result];
      }

}

?>