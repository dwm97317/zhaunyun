/**
 * 批量打印工具 - 前端调用模块
 * 
 * 功能：
 * 1. 将多个集运订单批量打印（同一个渠道商）
 * 2. 批量多渠道自动打印（自动识别快递渠道）
 * 
 * @author System
 * @version 2.0.0
 */

const OrderBatchPrinter = {
    /**
     * API 端点配置
     */
    apiEndpoint: '/store/inpack/orderbatchprinter',
    
    /**
     * 多渠道打印 API 端点
     */
    multiChannelEndpoint: '/store/inpack/batchPrint',
    
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
     * 批量多渠道打印（自动识别快递渠道）
     * 
     * @param {Array<number>} orderIds - 订单ID数组
     * @param {Object} options - 打印选项
     * @param {Function} options.onSuccess - 成功回调
     * @param {Function} options.onError - 失败回调
     * @returns {Promise<Object>} 打印结果
     */
    printMultiChannel: function(orderIds, options = {}) {
        const self = this;
        
        return $.ajax({
            url: this.multiChannelEndpoint,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                order_ids: orderIds
            }),
            dataType: 'json'
        }).done(function(response) {
            // code: 0=全部成功, 1=部分成功, -1=全部失败
            if ((response.code === 0 || response.code === 1) && response.data) {
                const data = response.data;
                
                // 显示结果摘要
                let msg, icon;
                if (data.failed === 0) {
                    msg = `批量打印成功！总数: ${data.total}`;
                    icon = 1;
                } else if (data.successful > 0) {
                    msg = `批量打印部分成功！总数: ${data.total}, 成功: ${data.successful}, 失败: ${data.failed}`;
                    icon = 0;
                } else {
                    msg = `批量打印失败！总数: ${data.total}, 失败: ${data.failed}`;
                    icon = 2;
                }
                
                layer.msg(msg, { icon: icon, time: 3000 });
                
                if (options.onSuccess) {
                    options.onSuccess(data);
                }
            } else {
                layer.msg(response.msg || '批量打印失败', { icon: 2 });
                if (options.onError) {
                    options.onError(response);
                }
            }
        }).fail(function(xhr, status, error) {
            layer.msg('网络错误: ' + error, { icon: 2 });
            if (options.onError) {
                options.onError({ error: error });
            }
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
                        
                        console.log('[批量打印] 收到响应数据:', data);
                        
                        // 收集所有成功的打印数据
                        const printDataList = [];
                        if (data.results && Array.isArray(data.results)) {
                            console.log('[批量打印] 处理 results 数组，长度:', data.results.length);
                            data.results.forEach(function(item, index) {
                                console.log('[批量打印] Result[' + index + ']:', {
                                    success: item.success,
                                    has_print_data: !!item.print_data,
                                    print_data_keys: item.print_data ? Object.keys(item.print_data) : []
                                });
                                if (item.success && item.print_data) {
                                    printDataList.push(item.print_data);
                                }
                            });
                        }
                        
                        console.log('[批量打印] 收集到打印数据数量:', printDataList.length);
                        
                        // 如果有打印数据，唤起打印插件
                        if (printDataList.length > 0) {
                            console.log('[批量打印] 准备唤起打印插件');
                            self._invokePrintPlugin(printDataList);
                        } else {
                            console.warn('[批量打印] 没有收集到打印数据');
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
            if (!printData) {
                console.warn('[批量打印] printData 为空，跳过');
                return;
            }
            
            console.log('[批量打印] 处理打印数据:', {
                mode: printData.mode,
                has_data: !!printData.data,
                has_printRequest: !!printData.printRequest,
                has_partnerID: !!printData.partnerID,
                data_type: typeof printData.data,
                data_keys: printData.data ? Object.keys(printData.data) : null
            });
            
            // 根据打印模式调用对应的打印方法
            if (printData.mode === 'sf_plugin') {
                // 顺丰云打印插件模式 - 使用 SCPPrint SDK
                console.log('[批量打印] 顺丰打印模式');
                
                if (typeof SCPPrint === 'undefined') {
                    console.error('[批量打印] SCPPrint SDK 未加载');
                    layer.msg('请先安装顺丰打印插件', {icon: 0});
                    return;
                }
                
                try {
                    var partnerID = printData.partnerID || '';
                    var sfPrintData = printData.data || {};
                    var printOptions = printData.printOptions || {};
                    var env = printData.env || 'sbox';
                    
                    if (!partnerID) {
                        console.error('[批量打印] 缺少 partnerID');
                        layer.msg('缺少客户编码(partnerID)', {icon: 2});
                        return;
                    }
                    
                    console.log('[批量打印] 顺丰打印参数:', {
                        partnerID: partnerID,
                        env: env,
                        has_printData: !!sfPrintData,
                        printData_type: typeof sfPrintData,
                        printData_keys: sfPrintData ? Object.keys(sfPrintData) : null,
                        printData_sample: sfPrintData ? JSON.stringify(sfPrintData).substring(0, 200) : null,
                        printOptions: printOptions
                    });
                    
                    // 创建 SCPPrint 实例（如果不存在）
                    if (!window.sfPrintInstance) {
                        window.sfPrintInstance = new SCPPrint({
                            partnerID: partnerID,
                            env: env,
                            notips: false
                        });
                        console.log('[批量打印] SCPPrint 实例已创建');
                    }
                    
                    // 读取渠道配置的打印机设置
                    var sfPrintOptions = printData.sfPrintOptions || {};
                    var defaultPrinter = sfPrintOptions.default_printer || '';
                    var enableSelectPrinter = sfPrintOptions.enable_select_printer || false;
                    
                    // 构建打印选项
                    var enablePreview = printOptions.enable_preview || false;
                    var sdkOptions = {
                        lodopFn: enablePreview ? 'PREVIEW' : 'PRINT'
                    };
                    
                    // 如果配置了默认打印机且启用了打印机选择
                    if (defaultPrinter && enableSelectPrinter) {
                        sdkOptions.printerName = defaultPrinter;
                        console.log('[批量打印] 顺丰使用指定打印机:', defaultPrinter);
                    } else {
                        console.log('[批量打印] 顺丰使用默认打印机');
                    }
                    
                    console.log('[批量打印] 调用 SCPPrint.print()');
                    
                    // 调用打印
                    window.sfPrintInstance.print(sfPrintData, function(result) {
                        console.log('[批量打印] 顺丰打印回调:', result);
                        
                        if (result.code === 1) {
                            console.log('[批量打印] 打印成功');
                        } else if (result.code === 2 || result.code === 3) {
                            layer.confirm('需要安装顺丰打印插件，是否立即下载？', {
                                btn: ['下载', '取消']
                            }, function() {
                                window.open(result.downloadUrl);
                            });
                        } else {
                            console.error('[批量打印] 打印失败:', result.msg);
                            layer.msg('打印失败: ' + (result.msg || '未知错误'), {icon: 2});
                        }
                    }, sdkOptions);
                    
                } catch (error) {
                    console.error('[批量打印] 顺丰打印异常:', error);
                    layer.msg('打印失败: ' + error.message, {icon: 2});
                }
                
            } else if (printData.mode === 'zto_cloud_print') {
                // 中通云打印模式
                console.log('[批量打印] 中通打印模式');
                console.log('[批量打印] 中通打印数据:', printData);
                
                // 中通云打印是服务端直接调用API完成的，前端只需要显示结果
                // 后端返回格式: { mode: 'zto_cloud_print', data: {...中通API返回的result...} }
                if (printData.data) {
                    var ztData = printData.data;
                    
                    // 中通API返回的数据结构：
                    // {
                    //   printSuccessList: ['运单号1', '运单号2', ...],
                    //   printErrorList: [{billCode: '运单号', errorMsg: '错误信息'}, ...]
                    // }
                    var successList = ztData.printSuccessList || [];
                    var errorList = ztData.printErrorList || [];
                    
                    var successCount = successList.length;
                    var failCount = errorList.length;
                    
                    console.log('[批量打印] 中通打印结果:', {
                        success: successCount,
                        fail: failCount,
                        successList: successList,
                        errorList: errorList
                    });
                    
                    if (failCount === 0 && successCount > 0) {
                        console.log('[批量打印] 中通打印全部成功');
                        layer.msg('中通打印成功 (' + successCount + '个)', {icon: 1});
                    } else if (successCount > 0 && failCount > 0) {
                        console.warn('[批量打印] 中通打印部分失败');
                        var errorMsg = '中通打印完成: 成功' + successCount + '个, 失败' + failCount + '个';
                        if (errorList.length > 0) {
                            errorMsg += '<br>失败原因: ' + errorList[0].errorMsg;
                        }
                        layer.msg(errorMsg, {icon: 0, time: 5000});
                    } else if (failCount > 0) {
                        console.error('[批量打印] 中通打印全部失败');
                        var errorMsg = '中通打印失败';
                        if (errorList.length > 0) {
                            errorMsg += ': ' + errorList[0].errorMsg;
                        }
                        layer.msg(errorMsg, {icon: 2, time: 5000});
                    } else {
                        console.warn('[批量打印] 中通打印无结果');
                        layer.msg('中通打印无结果', {icon: 0});
                    }
                } else {
                    console.error('[批量打印] 中通打印数据格式错误');
                    layer.msg('中通打印数据格式错误', {icon: 2});
                }
            } else if (printData.mode === 'jd_cloud_print') {
                // 京东云打印模式
                console.log('[批量打印] 京东打印模式');
                
                // 检查是否有 printRequest 数据
                if (!printData.printRequest) {
                    console.error('[批量打印] 缺少 printRequest 数据');
                    layer.msg('京东打印数据不完整', {icon: 0});
                    return;
                }
                
                // 读取渠道配置的打印机设置
                var jdPrintConfig = printData.jdPrintConfig || {};
                var printName = jdPrintConfig.printName || '';
                
                // 直接通过 WebSocket 连接到本地京东打印组件
                var printRequest = printData.printRequest;
                
                // 如果配置了打印机名称，添加到打印请求
                if (printName) {
                    printRequest.parameters = printRequest.parameters || {};
                    printRequest.parameters.printName = printName;
                    console.log('[批量打印] 京东使用指定打印机:', printName);
                } else {
                    console.log('[批量打印] 京东使用默认打印机');
                }
                
                console.log('[批量打印] 京东打印请求:', printRequest);
                
                // 尝试连接到本地京东打印组件
                var wsUrl = 'ws://127.0.0.1:9113';
                console.log('[批量打印] 尝试连接:', wsUrl);
                
                var socket = new WebSocket(wsUrl);
                var loadingIndex = layer.load(1, {shade: [0.3, '#000']});
                var connectionTimeout = setTimeout(function() {
                    if (socket.readyState !== WebSocket.OPEN) {
                        layer.close(loadingIndex);
                        socket.close();
                        console.error('[批量打印] WebSocket 连接超时');
                        layer.msg('连接京东打印组件超时，请检查组件是否已启动', {icon: 2, time: 5000});
                    }
                }, 5000); // 5秒超时
                
                socket.onopen = function() {
                    clearTimeout(connectionTimeout);
                    layer.close(loadingIndex);
                    console.log('[批量打印] 京东打印组件 WebSocket 已连接');
                    socket.send(JSON.stringify(printRequest));
                    layer.msg('打印数据已成功推送到本地组件', {icon: 1});
                    
                    // 1秒后自动关闭连接
                    setTimeout(function() {
                        socket.close();
                    }, 1000);
                };
                
                socket.onerror = function(err) {
                    clearTimeout(connectionTimeout);
                    layer.close(loadingIndex);
                    console.error('[批量打印] WebSocket 错误:', err);
                    console.error('[批量打印] WebSocket readyState:', socket.readyState);
                    layer.msg('未能连接到京东打印组件，请确保组件已启动 (ws://127.0.0.1:9113)', {icon: 2, time: 5000});
                };
                
                socket.onclose = function() {
                    console.log('[批量打印] WebSocket 连接已关闭');
                };
            } else if (printData.mode === 'pdf_url') {
                // PDF URL 模式：打开新窗口
                console.log('[批量打印] PDF URL 模式');
                window.open(printData.url, '_blank');
            } else {
                console.warn('[批量打印] 未识别的打印数据格式:', printData);
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
