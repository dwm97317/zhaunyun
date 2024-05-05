<?php

namespace app\common\service\package;

use app\common\model\Setting as SettingModel;
use app\common\model\Printer as PrinterModel;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\library\printer\Driver as PrinterDriver;

/**
 * 订单打印服务类
 * Class Printer
 * @package app\common\service\package
 */
class Printer
{
    /**
     * 执行订单打印
     * @param \app\common\model\BaseModel $package 订单信息
     * @param int $scene 场景
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function printTicket($package, $scene)
    {
        // 打印机设置
        $printerConfig = SettingModel::getItem('printer', $package['wxapp_id']);
        // dump($printerConfig);die;
        // 判断是否开启打印设置
        if (!$printerConfig['is_open']
            || !$printerConfig['printer_id']) {
            return false;
        }
        // 获取当前的打印机
        $printer = PrinterModel::detail($printerConfig['printer_id']);
    
        if (empty($printer) || $printer['is_delete']) {
            return false;
        }
        // 实例化打印机驱动
        $PrinterDriver = new PrinterDriver($printer);
            //   dump($PrinterDriver);die;
        // 获取订单打印内容
        $content = $this->getPrintContent($package);
       
        // 执行打印请求
        return $PrinterDriver->printTicket($content);
    }

    /**
     * 构建订单打印的内容
     * @param \app\common\model\BaseModel $package
     * @return string
     */
    private function getPrintContent($package)
    {
        // dump($package);die;
       $package['usermark'] = !empty($package['usermark'])?$package['usermark']:'1';
       $package['created_time'] = !empty($package['created_time'])?$package['created_time']:getTime();
      $printContent = "<PAGE w=\"80\" l=\"100\" ld=\"2\" pt=\"1\"> <LABEL id=\"1\" d=\"1\"><TEXT x=\"8\" y=\"10\" xw=\"2\" yh=\"2\" f=\"7\" r=\"0\" a=\"left\">唛头：".$package['usermark']."</TEXT><BARC x=\"8\" y=\"24\" ct=\"code128\" n=\"5\" w=\"3\" h=\"160\" s=\"2\" a=\"left\" r=\"0\">".$package['express_num']."</BARC><TEXT x=\"8\" y=\"53\" xw=\"2\" yh=\"2\" f=\"7\" r=\"0\" a=\"left\">".$package['created_time']."</TEXT></LABEL></PAGE>";
    //   $printContent = "<PAGE w=\"80\" l=\"100\" ld=\"2\" pt=\"1\"><LABEL id=\"1\" d=\"1\"><TEXT x=\"11\" y=\"10\" xw=\"2\" yh=\"2\" f=\"7\" r=\"0\" a=\"left\">
    //   唛头：".$package['usermark']."</TEXT><BARC x=\"10\" y=\"24\" ct=\"code128\" n=\"5\" w=\"3\" h=\"160\" s=\"0\" a=\"left\" r=\"0\">
    //   ".$package['express_num']."</BARC><TEXT x=\"9\" y=\"63\" xw=\"2\" yh=\"2\" f=\"7\" r=\"0\" a=\"left\">
    //   ".$package['created_time']."</TEXT><TEXT x=\"-47\" y=\"18\" xw=\"1\" yh=\"1\" f=\"0\" r=\"0\" a=\"left\"></TEXT><TEXT x=\"40\" y=\"47\" xw=\"2\" yh=\"2\" f=\"0\" r=\"0\" a=\"center\">
    //   ".$package['express_num']."</TEXT></LABEL></PAGE>";
 
        return $printContent;
    }

}