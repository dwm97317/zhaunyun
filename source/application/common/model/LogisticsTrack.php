<?php
namespace app\common\model;
use app\store\model\Inpack;

/**
 * 包裹日志Hook模型
 * Class OrderAddress
 * @package app\common\model
 */
class LogisticsTrack extends BaseModel
{    
    protected $name = 'logistics_track';
    protected $updateTime = false;
    
    public static function addhookLog($param){
        $model = new static;
        if (is_string($param)) {
            $data = json_decode($param, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON解析错误: ' . json_last_error_msg());
        }
        } elseif (is_array($param)) {
            $data = $param;
        } else {
            throw new Exception('无效的Webhook数据格式');
        }
        if (!isset($data['event']) || !isset($data['data'])) {
            return false;
        }
       
         if($data['event'] == "TRACKING_UPDATED"){
            $datas = [
                'express_num' => $data['data']['number'],
                'status_cn' =>   $data['data']['track_info']['latest_status']['status'],
                'logistics_describe' =>$data['data']['track_info']['latest_event']['description'],
                'longitude'=>$data['data']['track_info']['shipping_info']['shipper_address']['coordinates']['longitude'],
                'latitude'=>$data['data']['track_info']['shipping_info']['shipper_address']['coordinates']['latitude'],
                'country'=>$data['data']['track_info']['shipping_info']['shipper_address']['country'],
                'state'=>$data['data']['track_info']['shipping_info']['shipper_address']['state'],
                'city'=>$data['data']['track_info']['shipping_info']['shipper_address']['city'],
                'created_time' => date("Y-m-d H:i:s",time()),
                'wxapp_id'=>$param['wxapp_id'],
            ];
            
            $mapresult = $model->getLonLat($datas);
            if(count($mapresult)>0){
                $datas['latitude'] = $mapresult['lat'];
                $datas['longitude'] = $mapresult['lng'];
            }
            //如果包裹已到达，就修改包裹为已送达；
            if($datas['status_cn'] == 'Delivered'){
                (new Inpack())->where('t_order_sn',$datas['express_num'])->update(['status'=>7]);
                (new Inpack())->where('t2_order_sn',$datas['express_num'])->update(['status'=>7]);
            }
            
            $result = $model->where('status_cn',$datas['status_cn'])->where('express_num',$datas['express_num'])->find();
            if(empty($result)){
                return $model->insert($datas);
            }
            return false;
         }
    }
    
    public function getLonLat($params){
        $address = $params['country'].$params['state'].$params['city'];
        // $address = "广东省广州市天河区华景路61号";
        $apiKey = "AIzaSyBWWavMfwHvcGib7dWOFoGQXFs2Qj2I4LY";
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
        
        $response = $this->curlRequest($url);
          log_write($response);
        if($response['state']==1){
            $result = json_decode($response['result'], true);
                if(count($result['results'])>0){
                $lat = $result['results'][0]['geometry']['location']['lat'];
                $lng = $result['results'][0]['geometry']['location']['lng'];
                return ['lat'=>$lat,'lng'=>$lng];
            }
        } 
        return [];
    }
    
    function curlRequest($url, $data = '') {
	$return = array('state' => 0, 'message' => '', 'result' => '', 'errNo' => 0);
	try {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept-Language: zh-cn',
			'Connection: Keep-Alive',
			'Cache-Control: no-cache',
			'Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		//设置超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		//API返回数据
		$apiResult = curl_exec($ch);

		$errNo = curl_errno($ch);
		if ($errNo) {
			//返回错误码
			$return['errNo'] = $errNo;
			$errorStr = curl_error($ch);
			switch ((int)$errNo) {
				case 6: //避免一直发邮件 URL报错
					break;
				case 7: //无法通过 connect() 连接至主机或代理服务器
					break;
				case 28: //超时
					break;
				case 56: //接收网络数据失败
					break;
				default:
					break;
			}
			throw new Exception($errorStr);
		}
			
		curl_close($ch);
		$return['state'] = 1;
		//返回数据
		$return['result'] = $apiResult;
	} catch (Exception $e) {
		$return['state'] = 0;
		$return['message'] = $e->getMessage();
	}
	return $return;
}
   

}