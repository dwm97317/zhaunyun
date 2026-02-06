<?php

namespace app\api\model\store\shop;

use app\common\exception\BaseException;
use app\common\model\store\shop\ClerkComment as ClerkCommentModel;
use app\common\library\helper;

/**
 * 商品评价模型
 * Class Comment
 * @package app\api\model
 */
class ClerkComment extends ClerkCommentModel
{
    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User')->field(['user_id', 'nickName', 'avatarUrl']);
    }

     /**
     * 根据已完成订单商品 添加评价
     * @param Order $order
     * @param \think\Collection|OrderGoods $goodsList
     * @param $post
     * @return boolean
     * @throws \Exception
     */
    public function add($post)
    {
        // 生成 formData
        $formData = $this->formatFormDataPack($post);
        $formData['wxapp_id'] = self::$wxapp_id;
        if (empty($formData)){
            $this->error = '没有输入评价内容';
            return false;
        }
        return $this->transaction(function () use ($formData) {
            // 记录评价内容
            return $this->allowField(true)->save($formData);
        });
    }
    


    
     /**
     * 格式化 formData
     * @param string $post
     * @return array
     */
    private function formatFormDataPack($post)
    {
        $post['content'] = htmlspecialchars_decode($post['content']);
        return $post;
    }


    

}
