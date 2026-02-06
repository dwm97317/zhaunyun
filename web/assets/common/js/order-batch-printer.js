/**
 * 批量打印工具 - 前端调用模块
 * 
 * 功能：将多个集运订单批量打印（同一个渠道商）
 * 
 * @author System
 * @version 1.0.0
 */

const OrderBatchPrinter = {
    /**
     * API 端点配置
     */
    apiEndpoint: '/store/inpack/orderbatchprinter',
    
    /**
     * 批量打印订单（同步模式）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {number} ditchId - 渠道ID
     * @param {Object} options - 打印选项
     * @param {number} options.label - 标签尺寸（默认60）
     * @param {number} options.print_all - 是否打印全部包裹（默认0）
     * @param {string} options.waybill_no - 运单号（可选）
     * @returns {Promise<Object>} 打印结果
     */
    print: function(orderIds, ditchId, options = {}) {
        return this._request({
            order_ids: orderIds,
            ditch_id: ditchId,
            async: false,
            ...options
        });
    },
    
    /**
     * 批量打印订单（异步模式）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {number} ditchId - 渠道ID
     * @param {Object} options - 打印选项
     * @param {number} options.priority - 优先级（1-10，默认5）
     * @param {number} options.label - 标签尺寸（默认60）
     * @param {number} options.print_all - 是否打印全部包裹（默认0）
     * @returns {Promise<Object>} 任务信息
     */
    printAsync: function(orderIds, ditchId, options = {}) {
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
            url: '/store/inpack/asynctaskqueue',
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
     * 批量打印（带UI反馈）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {number} ditchId - 渠道ID
     * @param {Object} options - 选项
     * @param {boolean} options.async - 是否异步执行
     * @param {Function} options.onSuccess - 成功回调
     * @param {Function} options.onError - 失败回调
     * @param {Function} options.onProgress - 进度回调（仅异步模式）
     */
    printWithUI: function(orderIds, ditchId, options = {}) {
        const self = this;
        const isAsync = options.async || false;
        
        // 显示加载提示
        layer.msg('正在提交打印任务...', {
            icon: 16,
            shade: 0.3,
            time: 0
        });
        
        const printMethod = isAsync ? this.printAsync : this.print;
        
        printMethod.call(this, orderIds, ditchId, options)
            .done(function(result) {
                layer.closeAll('loading');
                
                if (result.code === 1) {
                    if (isAsync) {
                        // 异步模式：显示任务已提交
                        layer.msg('打印任务已提交，任务ID: ' + result.data.task_id, {
                            icon: 1,
                            time: 2000
                        });
                        
                        // 轮询任务状态
                        if (options.onProgress) {
                            self._pollTaskStatus(result.data.task_id, options.onProgress);
                        }
                    } else {
                        // 同步模式：处理打印数据并唤起打印插件
                        const data = result.data;
                        
                        // 收集所有成功的打印数据
                        const printDataList = [];
                        if (data.results && Array.isArray(data.results)) {
                            data.results.forEach(function(item) {
                                if (item.success && item.print_data) {
                                    printDataList.push(item.print_data);
                                }
                            });
                        }
                        
                        // 如果有打印数据，唤起打印插件
                        if (printDataList.length > 0) {
                            self._invokePrintPlugin(printDataList);
                        }
                        
                        // 显示打印结果
                        const msg = `打印完成！成功: ${data.success_count}, 失败: ${data.error_count}`;
                        layer.msg(msg, {
                            icon: data.error_count > 0 ? 2 : 1,
                            time: 2000
                        });
                    }
                    
                    if (options.onSuccess) {
                        options.onSuccess(result.data);
                    }
                } else {
                    layer.msg(result.msg || '打印失败', { icon: 2 });
                    
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
     * 唤起打印插件
     * 
     * @private
     * @param {Array} printDataList - 打印数据列表
     */
    _invokePrintPlugin: function(printDataList) {
        // 遍历所有打印数据，逐个唤起打印插件
        printDataList.forEach(function(printData) {
            if (!printData) return;
            
            // 根据不同的打印数据格式调用对应的打印方法
            if (printData.requestID) {
                // 顺丰云打印格式
                if (typeof window.sfCloudPrint === 'function') {
                    window.sfCloudPrint(printData);
                } else if (typeof window.cloudPrint === 'function') {
                    window.cloudPrint(printData);
                } else {
                    console.error('顺丰云打印插件未加载');
                }
            } else if (printData.cmd && printData.cmd === 'print') {
                // 中通云打印格式
                if (typeof window.do_print === 'function') {
                    window.do_print(printData);
                } else {
                    console.error('中通云打印插件未加载');
                }
            } else if (printData.taskId) {
                // 京东云打印格式
                if (typeof window.jdCloudPrint === 'function') {
                    window.jdCloudPrint(printData);
                } else {
                    console.error('京东云打印插件未加载');
                }
            } else {
                // 通用格式：尝试调用 LODOP
                if (typeof window.LODOP !== 'undefined') {
                    // 使用 LODOP 打印
                    console.log('使用 LODOP 打印', printData);
                } else {
                    console.warn('未识别的打印数据格式', printData);
                }
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
window.OrderBatchPrinter = OrderBatchPrinter;
