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
                                                <option value="<?= $item['ditch_id'] ?>" data-ditch-no="<?= isset($item['ditch_no'])?$item['ditch_no']:'' ?>"><?= $item['ditch_name'] ?>-<?= $item['ditch_no'] ?></option>
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
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 转运单 </label>
                                <div class="am-u-sm-4 am-u-end">
                                    <input type="text" id="t_order_sn" class="tpl-form-input" name="delivery[t_order_sn]"
                                           placeholder="请输入转运单号" required>
                                </div>
                                <button type="button" class="am-btn am-btn-secondary"><span onclick="toClick()">生成单号</span></button>
                            </div>
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
        var $selectNum = $('#selectnumber');
        $('#push-third').hide();
        if (!selectditch) return;
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
        var ProductList = $('#ProductList');   
        $.ajax({
               type:'post',
               url:"<?= url('store/tr_order/getProductList') ?>",
               data:{ditch_no:selectditch},
               dataType:'json',
               success:function (res) {
                   if (res.code==1){
                        if(res.data.length==0){
                            ProductList[0].innerHTML = '';
                            $('#product').hide();
                            if (ditchNo == 10009 || ditchNo == '10009') $('#push-third').show();
                        }else{
                            $('#product').show();
                            for (var i=0;i<res.data.length;i++){
                                ProductList.append('<option value="' + res.data[i]['product_id'] +'">' + res.data[i]['product_shortname'] + '</option>');
                            }
                        }
                   }else{
                       ProductList[0].innerHTML = '';
                       $('#product').hide();
                       if (ditchNo == 10009 || ditchNo == '10009') $('#push-third').show();
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
            $.post(hedanurl,{
                id: <?= $detail['id'] ?>,
                ditch_id: selectditch,
                product_id: ''
            }, function (result) {
                if (result.code == 1) {
                    if (result.data && result.data.ack == 'true') {
                        $("#t_order_sn").val(result.data.tracking_number || '');
                        layer.msg('推送成功，已回填转运单号');
                    } else {
                        $.show_error(result.data && result.data.message ? (result.data.message + '') : '推送失败');
                    }
                } else {
                    $.show_error((result.data && result.data.message) ? (result.data.message + '') : (result.msg || '推送失败'));
                }
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

    });
</script>
