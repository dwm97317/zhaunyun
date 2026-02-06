/**
 * 批量推送工具 - 前端调用模块
 * 
 * 功能：将多个集运订单批量推送到同一个渠道商
 * 
 * @author System
 * @version 1.0.0
 */

const OrderBatchPusher = {
    /**
     * API 端点配置
     */
    apiEndpoint: '/store/tr_order/orderbatchpusher',
    
    /**
     * 批量推送订单（同步模式）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {number} ditchId - 渠道ID
     * @param {Object} options - 推送选项
     * @param {string} options.product_id - 产品ID（可选）
     * @returns {Promise<Object>} 推送结果
     */
    push: function(orderIds, ditchId, options = {}) {
        return this._request({
            order_ids: orderIds,
            ditch_id: ditchId,
            async: false,
            ...options
        });
    },
    
    /**
     * 批量推送订单（异步模式）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {number} ditchId - 渠道ID
     * @param {Object} options - 推送选项
     * @param {number} options.priority - 优先级（1-10，默认5）
     * @param {string} options.product_id - 产品ID（可选）
     * @returns {Promise<Object>} 任务信息
     */
    pushAsync: function(orderIds, ditchId, options = {}) {
        return this._request({
            order_ids: orderIds,
            ditch_id: ditchId,
            async: true,
            priority: options.priority || 5,
            ...options
        });
    },
    
    /**
     * 查询异步任务状态
     * 
     * @param {number} taskId - 任务ID
     * @returns {Promise<Object>} 任务状态
     */
    getTaskStatus: function(taskId) {
        return $.ajax({
            url: '/store/tr_order/asynctaskqueue',
            type: 'GET',
            data: { 
                action: 'getTaskStatus',
                task_id: taskId 
            },
            dataType: 'json'
        });
    },
    
    /**
     * 发送请求
     * 
     * @private
     * @param {Object} data - 请求数据
     * @returns {Promise<Object>}
     */
    _request: function(data) {
        return $.ajax({
            url: this.apiEndpoint,
            type: 'POST',
            data: data,
            dataType: 'json'
        });
    },
    
    /**
     * 批量推送（带UI反馈）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {number} ditchId - 渠道ID
     * @param {Object} options - 选项
     * @param {boolean} options.async - 是否异步执行
     * @param {Function} options.onSuccess - 成功回调
     * @param {Function} options.onError - 失败回调
     * @param {Function} options.onProgress - 进度回调（仅异步模式）
     */
    pushWithUI: function(orderIds, ditchId, options = {}) {
        const self = this;
        const isAsync = options.async || false;
        
        // 显示加载提示
        layer.msg('正在提交推送任务...', {
            icon: 16,
            shade: 0.3,
            time: 0
        });
        
        const pushMethod = isAsync ? this.pushAsync : this.push;
        
        pushMethod.call(this, orderIds, ditchId, options)
            .done(function(result) {
                layer.closeAll('loading');
                
                if (result.code === 1) {
                    if (isAsync) {
                        // 异步模式：显示任务已提交
                        layer.msg('推送任务已提交，任务ID: ' + result.data.task_id, {
                            icon: 1,
                            time: 2000
                        });
                        
                        // 轮询任务状态
                        if (options.onProgress) {
                            self._pollTaskStatus(result.data.task_id, options.onProgress);
                        }
                    } else {
                        // 同步模式：显示推送结果
                        const data = result.data;
                        const msg = `推送完成！成功: ${data.success_count}, 失败: ${data.error_count}`;
                        
                        layer.msg(msg, {
                            icon: data.error_count > 0 ? 2 : 1,
                            time: 2000
                        });
                    }
                    
                    if (options.onSuccess) {
                        options.onSuccess(result.data);
                    }
                } else {
                    layer.msg(result.msg || '推送失败', { icon: 2 });
                    
                    if (options.onError) {
                        options.onError(result);
                    }
                }
            })
            .fail(function(xhr, status, error) {
                layer.closeAll('loading');
                layer.msg('网络错误: ' + error, { icon: 2 });
                
                if (options.onError) {
                    options.onError({ error: error });
                }
            });
    },
    
    /**
     * 轮询任务状态
     * 
     * @private
     * @param {number} taskId - 任务ID
     * @param {Function} callback - 回调函数
     */
    _pollTaskStatus: function(taskId, callback) {
        const self = this;
        const maxAttempts = 60; // 最多轮询60次（5分钟）
        let attempts = 0;
        
        const poll = function() {
            attempts++;
            
            self.getTaskStatus(taskId)
                .done(function(result) {
                    if (result.code === 1) {
                        const status = result.data.status;
                        
                        callback({
                            status: status,
                            data: result.data
                        });
                        
                        // 如果任务未完成且未超过最大尝试次数，继续轮询
                        if (status === 'pending' || status === 'processing') {
                            if (attempts < maxAttempts) {
                                setTimeout(poll, 5000); // 5秒后再次查询
                            } else {
                                callback({
                                    status: 'timeout',
                                    message: '任务查询超时'
                                });
                            }
                        }
                    }
                })
                .fail(function() {
                    if (attempts < maxAttempts) {
                        setTimeout(poll, 5000);
                    }
                });
        };
        
        // 延迟3秒后开始第一次查询
        setTimeout(poll, 3000);
    }
};

// 导出到全局
window.OrderBatchPusher = OrderBatchPusher;
