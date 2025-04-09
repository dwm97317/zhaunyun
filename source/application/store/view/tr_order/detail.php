<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="<?= url('store/trOrder/modify_save')?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑订单</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 集运线路 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[line_id]" id="line_select" 
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="caleAmount()" >
                                        <option value=""></option>
                                        <?php if (isset($line) && !$line->isEmpty()):
                                            foreach ($line as $item): ?>
                                                <option value="<?= $item['id'] ?>"
                                                data-vol-ratio="<?= $item['volumeweight'] ?>"  
                                                <?= $detail['line_id'] == $item['id'] ? 'selected' : '' ?>><?= $item['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">切换路线即可自动计算出对应运费</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-u-lg-2 am-form-label">分箱/子订单</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <div class="step_mode">
                                         <?php if (count($detail['packageitems'])>0): foreach ($detail['packageitems'] as $item): ?>
                                         <div>
                                             <div class="span">
                                                <input type="text" class="vlength tpl-form-input" onblur="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" name="data[item][length][]" value="<?= $item['length'] ?>" placeholder="长<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                <input type="text" class="vwidth tpl-form-input"  onblur="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" name="data[item][width][]" value="<?= $item['width'] ?>" placeholder="宽<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <input type="text" class="vheight tpl-form-input" onblur="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" name="data[item][height][]" value="<?= $item['height'] ?>" placeholder="高<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <select class="wvop" onchange="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" >
                                                    <option value="5000">5000</option>
                                                    <option value="6000">6000</option>
                                                    <option value="7000">7000</option>
                                                    <option value="8000">8000</option>
                                                    <option value="9000">9000</option>
                                                    <option value="10000">10000</option>
                                                    <option value="139">139</option>
                                                    <option value="166">166</option>
                                                 </select>
                                             </div>
                                             <div class="span">
                                                 <input class="volume_weight tpl-form-input" type="text" style="width:80px;border: 1px solid #c2cad8;" name="data[item][volume_weight][]" value="<?= $item['volume_weight'] ?>" placeholder="体积重<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <input type="text"  class="tpl-form-input weight" style="width:60px;border: 1px solid #c2cad8;" name="data[item][weight][]" value="<?= $item['weight'] ?>" placeholder="重量<?= $set['weight_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <input type="text"   class="tpl-form-input num"style="width:50px;border: 1px solid #c2cad8;" name="data[item][num][]"
                                                   value="1" placeholder="数量<?= $set['weight_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <input type="hidden" class="tpl-form-input" style="width:60px;border: 1px solid #c2cad8;" name="data[item][id][]" value="<?= $item['id'] ?>">
                                             </div>
                                            <div class="span jiahao">
                                                 <span class="cursor" onclick="addfreeRule(this)">+</span>
                                                 <span class="cursor" onclick="freeRuleDel(this)" style="margin-left:5px;">-</span>
                                            </div>
                                         </div>
                                         <?php endforeach; else: ?>
                                         <div>
                                            <button type="button" onclick="addfreeRule(this)" class="j-submit am-btn am-btn-secondary">添加分箱
                                            </button>
                                         </div>
                                         <?php endif; ?>
                                     </div>
                                </div>
                            </div>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">订单总重量(<?= $set['weight_mode']['unit'] ?>) </label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <div class="span">
                                         <input type="text"  <?= $detail['is_pay']==1?'disabled=true':'' ;?>  class="tpl-form-input" style="width:80px;color:red"  onblur="caleAmount()" oninput="caleAmount()" name="data[weight]"
                                           value="<?= $detail['weight']??'' ;?>" placeholder="请输入重量">
                                     </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">体积重</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="span">
                                        <input style="width:80px;color:red;" type="text" class="tpl-form-input" id="weigthV" name="data[volume]"
                                           value="<?= $detail['volume']??'' ;?>" placeholder="请输入价格">
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">计费重量</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" id="oWei" name="data[cale_weight]"
                                           value="<?= $detail['cale_weight']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            
                            
                            
                            <?php if (checkPrivilege('tr_order/freelist')): ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">集运路线费用</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" <?= $detail['is_pay']==1?'disabled=true':'' ;?> onchange="MathFree()" id="price" name="data[free]"
                                           value="<?= $detail['free']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">打包服务费用</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" <?= $detail['is_pay']==1?'disabled=true':'' ;?> id="pack_free" onchange="MathFree()" name="data[pack_free]"
                                           value="<?= $detail['pack_free']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">保险服务费用</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" <?= $detail['is_pay']==1?'disabled=true':'' ;?> id="insure_free" onchange="MathFree()" name="data[insure_free]"
                                           value="<?= $detail['insure_free']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">附加费用（如关税或其他额外费用）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" <?= $detail['is_pay']==1?'disabled=true':'' ;?> onchange="MathFree()" id="other_free"  name="data[other_free]"
                                           value="<?= $detail['other_free']??'' ;?>" placeholder="请输入价格">
                                    <div class="help-block">
                                        <small style="color:#ff6666;">包含其他增值费,清关费,等其他费用</small>
                                    </div>
                                </div>
                                 
                            </div>

                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">总费用（不可修改）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input disabled="" type="text" class="tpl-form-input" id="all"
                                           value="<?= $detail['total'] + $detail['insure_free'] ;?>" placeholder="">
                                           <small style="color:#ff6666;">总费用 = 集运路线费用 + 打包服务费用+ 附加费用（如需要减少总费用，请修改以上三个费用）</small>
                                </div>
                                
                            </div>
                             <?php endif ;?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">包裹图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php foreach ($detail['inpackimage'] as $key => $item): ?>
                                                <div class="file-item">
                                                    <a href="<?= $item['file_path'] ?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= $item['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="data[images][]"
                                                           value="<?= $item['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="help-block am-margin-top-sm">
                                        <small>尺寸750x750像素以上，大小2M以下 (可拖拽图片调整显示顺序 )</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">反馈备注</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[remark]"
                                           value="<?= $detail['remark']??'' ;?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <?php if($detail['status']==1):?>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 审核状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[verify]" value="1" data-am-ucheck
                                               >
                                        已查验
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[verify]" value="0" data-am-ucheck checked>
                                        仅保存数据
                                    </label>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">仅保存数据,不改变审核状态</small>
                                </div>
                                </div>
                            </div>
                            <?php endif;?>
                            <?php if(in_array($detail['status'],[2,3,4,5]) && $detail['is_pay']==2 ):?>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 支付方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[pay_type]" value="0" data-am-ucheck <?= $detail['pay_type']['value'] == 0 ? 'checked' : '' ?>>
                                        寄付
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[pay_type]" value="1" data-am-ucheck <?= $detail['pay_type']['value'] == 1 ? 'checked' : '' ?>>
                                        到付
                                    </label>
                                     <label class="am-radio-inline">
                                        <input type="radio" name="data[pay_type]" value="2" data-am-ucheck <?= $detail['pay_type']['value'] == 2 ? 'checked' : '' ?>>
                                        月结
                                    </label>
                                  
                                </div>
                            </div>
                            <?php endif; ?>
                           
                            <div class="am-form-group">
                                <input name="data[id]" type="hidden" value="<?= $detail['id']??'' ;?>">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">确认操作
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>


    $(function () {
        // 选择图片
        $('.upload-file').selectImages({
            name: 'data[images][]' , multiple: true
        }); 
       
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
        // 使用事件委托处理动态元素
        $(document).on('input change', '.step_mode .vlength, .step_mode .vwidth, .step_mode .vheight, .step_mode .wvop', function() {
            const row = $(this).closest('.step_mode > div');
            const volWeight = calculateSingleVolWeight(row);
            row.find('.volume_weight').val(volWeight.toFixed(2));
            updateAllWeights();
        });
        
        $(document).on('input change', '.step_mode .weight, .step_mode .num', function() {
            updateAllWeights();
        });
        // 设置初始体积重系数
        const initialRatio = $('#line_select option:selected').data('vol-ratio');
        if(initialRatio) currentVolRatio = parseFloat(initialRatio);
        
        console.log('初始体积重系数:', currentVolRatio);
    
        
        // 初始计算
        updateAllWeights();
    });

// 全局变量存储当前体积重系数
let currentVolRatio = 5000; // 默认值

// 切换路线时获取体积重系数
$('#line_select').change(function() {
    const selectedOption = $(this).find('option:selected');
    currentVolRatio = parseFloat(selectedOption.data('vol-ratio')) || 5000;
    console.log('切换路线，新体积重系数:', currentVolRatio);
    
    // 重新计算所有分箱体积重
    updateAllVolWeights();
    caleAmount();
});

// 更新所有分箱的体积重（使用新系数）
function updateAllVolWeights() {
    $('.step_mode > div').each(function() {
        const $row = $(this);
        const length = parseFloat($row.find('.vlength').val()) || 0;
        const width = parseFloat($row.find('.vwidth').val()) || 0;
        const height = parseFloat($row.find('.vheight').val()) || 0;
        
        if(length > 0 && width > 0 && height > 0) {
            const volWeight = (length * width * height / currentVolRatio).toFixed(2);
            $row.find('.volume_weight').val(volWeight);
            $row.find('.wvop').val(currentVolRatio); // 同时更新下拉框值
        }
    });
    
    // 更新汇总数据
    updateAllWeights();
}
    
function getweightvol(element) {
       // 更可靠的容器定位方式
    var container = $(element).closest('.step_mode > div');
    
    // 调试：检查容器查找是否正确
    console.log('Container length:', container.length);
    if(container.length === 0) {
        console.error('无法找到正确的容器元素');
        return;
    }
    
    // 从当前行获取值 - 更健壮的获取方式
    var length = parseFloat(container.find('.vlength').val()) || 0;
    var width = parseFloat(container.find('.vwidth').val()) || 0;
    var height = parseFloat(container.find('.vheight').val()) || 0;
    var wvop = parseFloat(container.find('.wvop').val()) || 5000;
    
    // 调试输出
    console.log('当前值:', {
        length: container.find('.vlength').val(),
        width: container.find('.vwidth').val(),
        height: container.find('.vheight').val(),
        wvop: container.find('.wvop').val()
    });
    
    // 确保所有尺寸值都有效
    if(length > 0 && width > 0 && height > 0) {
        // 计算体积重并保留2位小数
        var volumeWeight = (length * width * height / wvop).toFixed(2);
        container.find('.volume_weight').val(volumeWeight);
        
        console.log('计算结果:', volumeWeight); // 调试用
    } else {
        console.log('尺寸输入不完整，无法计算'); // 调试用
    }
}

// 修改计算单个体积重的函数
function calculateSingleVolWeight(row) {
    const length = parseFloat(row.find('.vlength').val()) || 0;
    const width = parseFloat(row.find('.vwidth').val()) || 0;
    const height = parseFloat(row.find('.vheight').val()) || 0;
    
    if(length > 0 && width > 0 && height > 0) {
        return (length * width * height / currentVolRatio);
    }
    return 0;
}

// 实时更新所有重量数据
function updateAllWeights() {
    let totalActualWeight = 0;
    let totalVolWeight = 0;
    
    $('.step_mode > div').each(function() {
        // 确保获取最新重量值
        const weight = parseFloat($(this).find('.weight').val()) || 0;
        const quantity = parseFloat($(this).find('.num').val()) || 1;
        totalActualWeight += weight * quantity;
        
        // 计算当前分箱体积重（使用最新尺寸值）
        const volWeight = calculateSingleVolWeight($(this));
        $(this).find('.volume_weight').val(volWeight.toFixed(2));
        totalVolWeight += volWeight;
    });
    
    // 更新显示
    $('input[name="data[weight]"]').val(totalActualWeight.toFixed(2));
    $('#weigthV').val(totalVolWeight.toFixed(2));
    
    // 计费重量取较大值
    const chargeableWeight = Math.max(totalActualWeight, totalVolWeight);
    $('#oWei').val(chargeableWeight.toFixed(2));
    
    console.log('最终计算结果:', { // 调试用
        totalActualWeight,
        totalVolWeight,
        chargeableWeight
    });
    
    // 自动计算运费
    caleAmount();
}

    
    // 添加新分箱行
function addfreeRule(btn) {
    var container = $(btn).closest('.step_mode');
    var newRow = $('<div>').addClass('box-row').html(`
        <div class="span">
            <input type="text" class="vlength tpl-form-input" onblur="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" name="data[item][length][]" placeholder="长<?= $set['size_mode']['unit'] ?>">
        </div>
        <div class="span">
            <input type="text" class="vwidth tpl-form-input" onblur="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" name="data[item][width][]" placeholder="宽<?= $set['size_mode']['unit'] ?>">
        </div>
        <div class="span">
            <input type="text" class="vheight tpl-form-input" onblur="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;" name="data[item][height][]" placeholder="高<?= $set['size_mode']['unit'] ?>">
        </div>
        <div class="span">
            <select class="wvop" onchange="getweightvol(this)" style="width:60px;border: 1px solid #c2cad8;">
                <option value="5000">5000</option>
                <option value="6000">6000</option>
                <option value="7000">7000</option>
                <option value="8000">8000</option>
                <option value="9000">9000</option>
                <option value="10000">10000</option>
                <option value="139">139</option>
                <option value="166">166</option>
            </select>
        </div>
        <div class="span">
            <input class="volume_weight tpl-form-input" type="text" style="width:80px;border: 1px solid #c2cad8;" name="data[item][volume_weight][]" placeholder="体积重<?= $set['size_mode']['unit'] ?>" readonly>
        </div>
        <div class="span">
            <input type="text" class="tpl-form-input weight" style="width:60px;border: 1px solid #c2cad8;" name="data[item][weight][]" placeholder="重量<?= $set['weight_mode']['unit'] ?>">
        </div>
        <div class="span">
            <input type="text"  class="tpl-form-input num" style="width:50px;border: 1px solid #c2cad8;" name="data[item][num][]" value="1" placeholder="数量">
        </div>
        <div class="span jiahao">
            <span class="cursor" onclick="addfreeRule(this)">+</span>
            <span class="cursor" onclick="freeRuleDel(this)" style="margin-left:5px;">-</span>
        </div>
    `);
    // 绑定事件
    newRow.find('input, select').on('change blur', function() {
        updateAllWeights();
    });
    container.append(newRow);
}

// 删除分箱功能（增强版）
function freeRuleDel(btn) {
    if(!confirm('确定要删除这个分箱吗？')) return;
    
    const container = $(btn).closest('.step_mode > div');
    const itemId = container.find('input[name="data[item][id][]"]').val();
    
    // 显示加载状态
    const $btn = $(btn);
    $btn.prop('disabled', true).html('<i class="am-icon-spinner am-icon-spin"></i>');
    
    // 如果有ID则请求后端删除
    const deletePromise = itemId ? 
        $.post("<?= url('store/trOrder/deleteInpackItem') ?>", {id: itemId}) : 
        Promise.resolve({code: 1});
    
    deletePromise.then(res => {
        if(res.code !== 1) throw new Error(res.msg || '删除失败');
        
        // 从DOM移除
        container.remove();
        
        // 如果删光了最后一个，添加空分箱
        if($('.step_mode > div').length === 0) {
            $('.step_mode').html('<div><button type="button" onclick="addfreeRule(this)" class="j-submit am-btn am-btn-secondary">添加分箱</button></div>');
        }
        
        // 更新重量
        updateAllWeights();
    }).catch(err => {
        alert(err.message);
        console.error('删除失败:', err);
    }).finally(() => {
        $btn.prop('disabled', false).html('-');
    });
}
    
    function MathFree(){
        // var price = $('#price')[0].value;
        var price = parseFloat($('#price')[0].value.replace(/\,/g, ''), 10);
        var other_free = $('#other_free')[0].value;
        var pack_free = $('#pack_free')[0].value;
        var insure_free = $('#insure_free')[0].value;
        console.log(price,456);
        var total = Math.floor(price *100 )+ Math.floor(pack_free *100)+ Math.floor(other_free *100) + Math.floor(insure_free *100);  
        $("#all").val(total/100)
    }
    
    // 实时计算
    function caleAmount(){
        var is_pay = <?= $detail['is_pay'] ;?>     
        if(is_pay==1){
         return false;
        }    
       var form = $("#my-form").serializeArray();
       var newdata = [];
       var is_auto_free = <?=$is_auto_free?>;
       console.log(is_auto_free,5);
       if(is_auto_free==0){
           return false;
       }
       form.map(function(val,key){
			newdata[val.name]=val.value;
	   });
       $.ajax({
          type:"POST",
          url:"<?= url('store/trOrder/caleAmount')?>",
          data:{pid:newdata['data[id]'],line_id:newdata['data[line_id]'],
          weight:newdata['data[cale_weight]']},
          dataType:'json',
          success:function(res){
             if (res.code==1){
                 $('#price').val(res.msg.price);
                 $('#pack_free').val(res.msg.packfree);
                 $('#insure_free').val(res.msg.insure_free);
                 var other_free = $('#other_free').val();
                 var pack_free = $('#pack_free').val();
                 var insure_free = $('#insure_free').val();
                 var num = parseFloat(res.msg.price);
                 var total = Number(num) + Number(pack_free) + Number(other_free) + Number(insure_free);
                 $("#all").val(total)
             } 
          }
       })    
    }
</script>
<style>
    .wd {
        width: 200px;
    }
    .country-search-panle {
        width: 100%; height: auto; max-height: 300px;
        background: #fff;
        border: 1px solid #eee;
        position: absolute;
        top:25px; left: 0; z-index: 999;
    }
    .country-search-title { height: 25px; line-height: 25px; font-size: 14px; padding-left: 10px;}
    .country-search-content { width: 100%; height: auto; }
    .country-search-content p { padding-left: 10px; height: 25px; cursor: pointer; line-height: 25px; font-size: 14px;}
    .country-search-content p:hover { background: #0b6fa2; color: #fff;}
    .hidden { display: none}
    .show { display: block;}
    .jiahao span { display: inline-block; padding:0 5px; font-size:14px; background:#3bb4f2; margin-right:5px; color:#fff; }
    .category span { display: inline-block; padding:0 5px; font-size:14px; background:#3bb4f2; margin-right:5px; color:#fff; }
    .cursor { cursor:pointer;}
    
    .category-layer { width:100%; height:100%; position:fixed; display:none; background:rgba(0,0,0,.5); top:0; left:0;}
    .category-dialog { background:#fff; width: 450px; min-height:200px; border-radius:5px; position:absolute; top:40%; left:50%; margin-right:-175px; padding:5px;}
    .category-title { height:30px; line-height:30px; font-size:13px; padding:5px;}
    .category-content { width:95%; margin:0 auto;}
    
    .category-item { width:100%; height:auto; margin-bottom:5px;}
    .category-name { font-size:14px; color:#666;}
    
    .category-content ul { width:100%; height:auto;}
    .category-content ul li { display:inline-block; background:#eee; height:25px; line-height:25px; border-radius:5px; cursor:pointer; padding:0 8px; margin-right:5px; font-size:13px;}
     .category-content ul li.action { background:#3bb4f2;color:#fff;}
     
     .category-btn { width:95%; margin: 30px auto 0 auto;}
     .category-btn a { display:inline-block; width:auto; padding:0 5px; font-size:13px;}
     
     .span { display:inline-block; font-size:13px; color:#666; margin-bottom:10px;}
</style>