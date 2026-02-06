<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\Inpack;
use app\common\model\Ditch;
use app\common\library\Ditch\Sf;
use app\common\service\Message;

class TraceSync extends Command
{
    protected function configure()
    {
        $this->setName('trace:sync')
             ->setDescription('Sync SF Express trace and push WeChat notifications');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("Start sync trace...");
        
        // 查找 Inpack 运单: 状态=9(已发货) 且 未推送签收 的
        $list = Inpack::where('status', 9)
            ->where('is_push_signed', 0)
            ->where('t_order_sn', '<>', '')
            ->whereNotNull('t_order_sn')
            ->where('is_delete', 0)
            ->select();
            
        $output->writeln("Found " . count($list) . " orders.");
        
        foreach ($list as $inpack) {
            $this->process($inpack, $output);
        }
        
        $output->writeln("Done.");
    }
    
    private function process($inpack, $output)
    {
        try {
            if (!$inpack['ditch_id']) return;
            
            // 获取渠道
            $ditch = Ditch::get($inpack['ditch_id']);
            // 仅处理 SF (Type 4)
            if (!$ditch || $ditch['ditch_type'] != 4) {
                return;
            }
            
            $config = [
                'key'    => $ditch['app_key'],
                'token'  => $ditch['app_token'],
                'apiurl' => isset($ditch['api_url']) ? $ditch['api_url'] : '',
                'customer_code' => isset($ditch['customer_code']) ? $ditch['customer_code'] : '',
            ];
            
            $Sf = new Sf($config);
            $statusData = $Sf->getLastStatus($inpack['t_order_sn']);
            
            if (!$statusData) {
                 $output->writeln("[{$inpack['id']}] No trace data for {$inpack['t_order_sn']}.");
                 return;
            }
            
            $code = $statusData['code'];
            $status = $statusData['status'];
            $msg = $statusData['msg'];
            $time = $statusData['time'];
            
            // 更新数据库状态
            if ($inpack['last_trace_code'] != $code) {
                $inpack->save([
                    'last_trace_code' => $code,
                    'last_trace_time' => $time
                ]);
                $output->writeln("[{$inpack['id']}] Updated Code: $code ($status)");
            }
            
            // 派送通知
            if ($status === 'delivering' && $inpack['is_push_delivered'] == 0) {
                $output->writeln("[{$inpack['id']}] Sending Delivery Push...");
                $res = Message::send('trace.delivery', [
                    'inpack' => $inpack,
                    'msg'    => $msg
                ]);
                if ($res) {
                    $inpack->save(['is_push_delivered' => 1]);
                    $output->writeln("  -> Success.");
                } else {
                     $output->writeln("  -> Failed (Check Template config).");
                }
            }
            // 签收通知
            elseif ($status === 'signed' && $inpack['is_push_signed'] == 0) {
                $output->writeln("[{$inpack['id']}] Sending Signed Push...");
                 $res = Message::send('trace.signed', [
                    'inpack' => $inpack,
                    'time'   => $time
                ]);
                if ($res) {
                    $inpack->save(['is_push_signed' => 1]);
                    $output->writeln("  -> Success.");
                } else {
                     $output->writeln("  -> Failed (Check Template config).");
                }
            }
            
        } catch(\Exception $e) {
            $output->writeln("Error ID:{$inpack['id']} " . $e->getMessage());
        }
    }
}
