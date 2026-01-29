<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="<?= url('/store/tr_order/deliverySave') ?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货信息</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 包裹单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <p class="am-form-static"><?= $detail['order_sn'] ?></p>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 运输方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="delivery[transfer]" value="1" data-am-ucheck checked onchange="onChange('c1')"
                                               >
                                        运输商
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="delivery[transfer]" value="0" data-am-ucheck   onchange="onChange('c2')">
                                        自有物流（某些物流的代理）
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select name="delivery[tt_number]"  id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['express_code'] ?>"><?= $item['express_name'] ?>-<?= $item['express_code'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：选择自有物流17track不可查，请选择正确的物流商，否则国际单号无法查询；</small>
                                </div>
                                </div>
                                
                            </div>
                            <div class="am-form-group c" id="c2" style="display: none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 渠道商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="delivery[t_number]" id="selectditch" onchange="selectDitch(this)"  data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($ditchlist)):
                                            foreach ($ditchlist as $item): ?>
                                            <option value="<?= $item['ditch_id'] ?>" data-ditch-type="<?= isset($item['ditch_type']) ? $item['ditch_type'] : '' ?>" data-ditch-no="<?= isset($item['ditch_no'])?$item['ditch_no']:'' ?>"><?= $item['ditch_name'] ?>-<?= $item['ditch_no'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     
                                     
                                </div>
                            </div>
                            <div class="am-form-group" id="choosenumber" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 选择单号 </label>
                                <div class="am-u-sm-4 am-u-end">
                                    <select style="display:none;" name="delivery[t_order_sn]" id="selectnumber"  onchange="selectNumberS(this)"  data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择单号</option>
                                     </select>
                                </div>
                                
                            </div>
                            <div class="am-form-group" id="product" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 选择渠道 </label>
                                <div class="am-u-sm-4 am-u-end">
                                    <select id="ProductList"   data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择渠道</option>
                                     </select>
                                     <button type="button" class="am-btn am-btn-success"><span onclick="tuiClick()">推送至渠道系统</span></button>
                                </div>
                                
                            </div>
                            <div class="am-form-group" id="push-third" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 推送到第三方 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <button type="button" class="am-btn am-btn-success am-radius"><span onclick="pushToThird()">推送到第三方系统</span></button>
                                    <small class="am-margin-left-sm">将订单推送至中通等渠道创建运单，成功后可自动回填转运单号</small>
                                </div>
                            </div>
                            <?php 
                            // 检查是否有分箱信息
                            $isMultiBox = isset($detail['packageitems']) && count($detail['packageitems']) > 0;
                            ?>
                            
                            <!-- 主单转运单号 (兼容字段) -->
                            <!-- 如果是多箱模式，此字段隐藏自动同步；如果是单箱模式，此字段正常显示 -->
                            <div class="am-form-group" style="<?= $isMultiBox ? 'display:none;' : '' ?>">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 转运单 </label>
                                <div class="am-u-sm-4 am-u-end">
                                    <input type="text" id="t_order_sn" class="tpl-form-input" name="delivery[t_order_sn]"
                                           value="<?= isset($detail['t_order_sn']) ? $detail['t_order_sn'] : '' ?>"
                                           placeholder="请输入转运单号" <?= $isMultiBox ? '' : 'required' ?>>
                                </div>
                                <button type="button" class="am-btn am-btn-secondary"><span onclick="toClick()">生成单号</span></button>
                            </div>
                            
                            <?php if($isMultiBox): ?>
                            <!-- 多箱模式：分箱单号列表 -->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 包裹分箱信息 </label>
                                <div class="am-u-sm-9 am-u-end" style="padding-left: 0;">
                                    <?php foreach($detail['packageitems'] as $index => $item): ?>
                                    <div class="am-u-sm-12 am-margin-bottom-xs">
                                        <label class="am-u-sm-3 am-form-label am-text-sm" style="font-weight:normal; text-align: right;">
                                            <?php if($index === 0): ?>
                                            <span class="am-badge am-badge-success am-radius">母单</span> 
                                            <?php endif; ?>
                                            箱<?= $index + 1 ?> (ID:<?= $item['id'] ?>)
                                        </label>
                                        <div class="am-u-sm-5 am-u-end">
                                            <input type="text" 
                                                   class="tpl-form-input son-tracking-input <?= $index === 0 ? 'box-first-input' : '' ?>" 
                                                   style="display:inline-block;"
                                                   name="delivery[sonitem][<?= $item['id'] ?>][t_order_sn]"
                                                   value="<?= isset($item['t_order_sn']) ? $item['t_order_sn'] : '' ?>"
                                                   data-box-id="<?= $item['id'] ?>"
                                                   placeholder="请输入分箱运单号" 
                                                   <?= $index === 0 ? 'required' : '' ?> >
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="am-form-group">
                                <input type="hidden" name="delivery[type]" value="delivery"/>
                                <input type="hidden" name="delivery[id]" value="<?= $detail['id'] ?>"/>
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<script>
    var selectDitch = function(_this){
        var selectditch = $('#selectditch option:selected').val();
        var ditchNo = $('#selectditch option:selected').data('ditch-no');
        var ditchType = $('#selectditch option:selected').data('ditch-type');
        console.log('selectDitch check:', {ditchNo: ditchNo, ditchType: ditchType});
        var $selectNum = $('#selectnumber');
        
        // 重置所有相关区域
        $('#push-third').hide();
        $('#product').hide();
        $('#choosenumber').hide();
        
        if (!selectditch) return;
        
        // 类型转换，确保比较正确
        ditchType = parseInt(ditchType);
        
        // 顺丰(4) 或 中通(3) 或 特定编号：直接显示推送按钮
        if (ditchType === 4 || ditchType === 3 || ditchNo == 10009) {
            $('#push-third').show();
        }

        // 获取单号列表（保留此逻辑，以防万一有预存单号）
        $.ajax({
               type:'post',
               url:"<?= url('store/setting.ditch/getdicthNumberList') ?>",
               data:{ditch_no:selectditch},
               dataType:'json',
               success:function (res) {
                   if (res.code==1){
                        if(res.data.length==0){
                            $selectNum[0].innerHTML = '';
                            $('#choosenumber').hide();
                        }else{
                            $('#choosenumber').show();
                            for (var i=0;i<res.data.length;i++){
                                $selectNum.append('<option value="' + res.data[i]['ditch_number'] +'">' + res.data[i]['ditch_number'] + '</option>');
                            }
                        }
                   }else{
                       $('#choosenumber').hide();
                   }
               }
           });
           
        // 如果是顺丰或中通，不需要获取产品列表，直接结束
        if (ditchType === 4 || ditchType === 3 || ditchNo == 10009) {
            return;
        }
           
        var ProductList = $('#ProductList');   
        $.ajax({
               type:'post',
               url:"<?= url('store/tr_order/getProductList') ?>",
               data:{ditch_no:selectditch},
               dataType:'json',
               success:function (res) {
                   // 清空此前内容
                   ProductList[0].innerHTML = '<option value="">选择渠道</option>';
                   
                   if (res.code==1){
                        if(res.data.length==0){
                            $('#product').hide();
                            // 兜底逻辑：如果API没返回产品，且符合特定条件，显示推送按钮
                            // (虽然上面已经处理了主要类型的显示，这里保留作为兼容)
                            if (ditchNo == 10009 || ditchNo == '10009' || ditchType == 3 || ditchType == 4) $('#push-third').show();
                        }else{
                            $('#product').show();
                            for (var i=0;i<res.data.length;i++){
                                ProductList.append('<option value="' + res.data[i]['product_id'] +'">' + res.data[i]['product_shortname'] + '</option>');
                            }
                        }
                   }else{
                       $('#product').hide();
                       if (ditchNo == 10009 || ditchNo == '10009' || ditchType == 2 || ditchType == 3 || ditchType == 4) $('#push-third').show();
                   }
               }
           });
    }

    function selectNumberS(){
        var selectnumber = $('#selectnumber option:selected').val();
        $('#t_order_sn').val(selectnumber);
    }

    
    function toClick(){
        var hedanurl = "<?= url('store/tr_order/createbatchname') ?>";
        layer.confirm('请确定是否生成单号', {title: '生成运单号'}
        , function (index) {
            $.post(hedanurl,{id:<?= $detail['id'] ?>}, function (result) {
                if(result.code == 1){
                    $("#t_order_sn").val(result.data);
                }else{
                   $.show_error(result.msg); 
                }
            });
            layer.close(index);
        });        
    }
    
    function tuiClick(){
        var hedanurl = "<?= url('store/tr_order/sendtoqudaoshang') ?>";
        var selectditch = $('#selectditch option:selected').val();
        var product_id = $('#ProductList option:selected').val() || '';
        layer.confirm('请确定是否将订单推送至渠道商系统', {title: '推送订单至渠道商系统'}
        , function (index) {
            $.post(hedanurl,{
                id:<?= $detail['id'] ?>,
                'ditch_id':selectditch,
                'product_id':product_id
            }, function (result) {
                if(result.code == 1){
                    if(result.data.ack=='true'){
                        $("#t_order_sn").val(result.data.tracking_number);
                        layer.msg('推送成功，已回填转运单号');
                    }else{
                        $.show_error(result.data && result.data.message ? decodeURIComponent(result.data.message) : '推送失败');
                    }
                }else{
                    $.show_error((result.data && result.data.message) ? decodeURIComponent(result.data.message) : (result.msg || '推送失败'));
                }
            });
            layer.close(index);
        });
    }

    function pushToThird(){
        var selectditch = $('#selectditch option:selected').val();
        if (!selectditch) {
            $.show_error('请先选择渠道商');
            return;
        }
        var hedanurl = "<?= url('store/tr_order/sendtoqudaoshang') ?>";
        layer.confirm('确定将订单推送到第三方系统（如中通）创建运单？', {title: '推送到第三方系统'}
        , function (index) {
            var loadIndex = layer.load(1); // 显示加载中
            $.post(hedanurl,{
                id: <?= $detail['id'] ?>,
                ditch_id: selectditch,
                product_id: ''
            }, function (result) {
                layer.close(loadIndex); 
                if (result.code == 1) {
                    // 兼容 boolean 和 string 类型的 ack
                    var isAck = result.data && (result.data.ack === 'true' || result.data.ack === true);
                    if (isAck) {
                        var trackingNum = result.data.tracking_number;
                        // 确保输入框存在并赋值
                        var $input = $("#t_order_sn");
                        if ($input.length > 0) {
                            $input.val(trackingNum);
                            layer.msg('推送成功，已回填转运单号: ' + trackingNum);
                        } else {
                            layer.msg('推送成功，但未找到单号输入框，单号: ' + trackingNum);
                        }
                        
                        // 自动回填子单号
                        if (result.data.sub_tracking_numbers && result.data.sub_tracking_numbers.length > 0) {
                            $.each(result.data.sub_tracking_numbers, function(i, item) {
                                 var $input = $('input[data-box-id="' + item.id + '"]');
                                 if ($input.length > 0) {
                                     $input.val(item.tn);
                                     // 如果是箱1，触发 input 事件以确保同步
                                     if ($input.hasClass('box-first-input')) {
                                         $input.trigger('input');
                                     }
                                 }
                            });
                            layer.msg('推送成功，已回填 ' + result.data.sub_tracking_numbers.length + ' 个分箱单号');
                        } else {
                            // 兜底：如果没有返回详细子单列表，尝试把主单号填入第一箱（多箱模式下）
                            var $firstBox = $('.box-first-input');
                            if ($firstBox.length > 0 && trackingNum) {
                                $firstBox.val(trackingNum);
                            }
                        }
                    } else {
                        var errMsg = (result.data && result.data.message) ? result.data.message : '推送失败';
                        try { errMsg = decodeURIComponent(errMsg); } catch(e){}
                        $.show_error(errMsg);
                    }
                } else {
                    var errMsg = (result.data && result.data.message) ? result.data.message : (result.msg || '推送失败');
                    try { errMsg = decodeURIComponent(errMsg); } catch(e){}
                    $.show_error(errMsg);
                }
            }, 'json')
            .fail(function(xhr) {
                layer.close(loadIndex);
                var errInfo = '服务器响应异常';
                if (xhr && xhr.responseText) {
                    // 尝试截取错误信息的前150个字符，方便排查（可能是PHP报错或BOM头）
                    var resp = xhr.responseText.replace(/<[^>]+>/g, ''); // 去除HTML标签
                    errInfo += ': ' + resp.substring(0, 150);
                }
                $.show_error(errInfo);
            });
            layer.close(index);
        });
    } 
    /**
     * 设置坐标
     */
    function setCoordinate(value) {
        var $coordinate = $('#coordinate');
        $coordinate.val(value);
        // 触发验证
        $coordinate.trigger('change');
    }
</script>
<script>

  
    function onChange(tab){
       $('.c').hide();
       $('#'+tab).show();
       if (tab === 'c1') {
           $('#choosenumber, #product, #push-third').hide();
       }
    }
    function getkey(e){
       console.log(e); 
    }

    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        // 页面加载后自动触发一次检查，确保按钮显示（延迟500ms等待UI初始化）
        setTimeout(function(){ 
            if(window.selectDitch) window.selectDitch(); 
        }, 500);

        // 多箱模式下，箱1输入同步到主单号
        $(document).on('input propertychange', '.box-first-input', function() {
            var val = $(this).val();
            // console.log('Syncing box-1 to master:', val);
            $('#t_order_sn').val(val);
        });
        // 初始同步
        if ($('.box-first-input').length > 0) {
            $('#t_order_sn').val($('.box-first-input').val());
        }

    });
</script>
