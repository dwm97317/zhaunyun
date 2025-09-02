<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加增值服务</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">增值服务名称</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[name]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">增值服务说明</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[desc]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">增值服务类型</label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[type]" value="10" data-am-ucheck checked>
                                        重量模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[type]" value="20" data-am-ucheck>
                                        长度模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[type]" value="30" data-am-ucheck>
                                        偏远模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[type]" value="40" data-am-ucheck>
                                        税费模式
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 运输形式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php if (count($linecategory)>0): foreach ($linecategory as $key =>$item): ?>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_category_id]"  value="<?= $item['category_id'] ?>" data-am-ucheck  <?= $key==0?'checked':'' ?>>
                                        <?= $item['name'] ?>
                                    </label>
                                    <?php endforeach; endif; ?>
                            
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">归属国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="line[country_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($countryList) && !$countryList->isEmpty()):
                                            foreach ($countryList as $item): ?>
                                                <?php if(isset($data['country'])): ?>
                                                   <option value="<?= $item['id'] ?>" <?= $data['country_id'] == $item['id'] ? 'selected' : '' ?> ><?= $item['title'] ?></option>
                                                <?php else: ?>  
                                                   <option value="<?= $item['id'] ?>" ><?= $item['title'] ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- 区间计费规则（所有模式都显示） -->
                            <div class="am-form-group" id="area_mode">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">区间计费规则</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <table class="am-table">
                                        <thead>
                                            <tr>
                                                <th>开始值</th>
                                                <th>结束值</th>
                                                <th <th id="fee_header">费用</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody class="area_mode">
                                            <tr>
                                                <td><input type="text" name="line[weight_start][]" placeholder="输入起始值"></td>
                                                <td><input type="text" name="line[weight_max][]" placeholder="输入结束值"></td>
                                                <td><input type="text" name="line[weight_price][]" placeholder="输入所需费用"></td>
                                                <td>
                                                    <button type="button" class="am-btn am-btn-xs am-btn-danger" onclick="freeRuleDelarea(this)">删除</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="am-btn am-btn-xs am-btn-success" onclick="addfreeRulearea()">新增规则</button>
                                </div>
                            </div>
                            
                            <!-- 偏远地区设置（仅偏远模式显示） -->
                            <div class="am-form-group" id="remote_mode" style="display:none;">
                                <div class="am-u-sm-9 am-u-end ">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-3 am-form-label form-require">偏远地区列表</label>
                                        <div class="am-u-sm-9 am-u-end">
                                            <textarea class="tpl-form-input" name="line[remote_areas]" rows="5" 
                                                      placeholder="请输入偏远地区列表，每行一个地区" required></textarea>
                                            <small class="am-text-warning">每行输入一个偏远地区名称</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否启用</label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="0" data-am-ucheck checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="1" data-am-ucheck>
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    // 根据选择的类型显示不同的表单部分
    $('input[name="line[type]"]').change(function() {
        var selectedValue = $(this).val();
        
        // 更新费用列标题和占位符
        if (selectedValue == '40') {
            // 税务模式 - 显示货值百分比
            $('#fee_header').text('货值百分比(%)');
            $('input[name^="line[weight_price]"]').attr('placeholder', '输入货值百分比');
        } else {
            // 其他模式 - 显示费用
            $('#fee_header').text('费用');
            $('input[name^="line[weight_price]"]').attr('placeholder', '输入所需费用');
        }
        
        if (selectedValue == '30') {
            // 偏远模式 - 显示偏远地区设置
            $('#remote_mode').show();
        } else {
            // 其他模式 - 隐藏偏远地区设置
            $('#remote_mode').hide();
        }
    });

    function addfreeRulearea(){
        var amformItem = document.getElementsByClassName('area_mode')[0];
        var Item1 = document.createElement('tr');
        
        // 根据当前选择的类型设置占位符
        var selectedType = $('input[name="line[type]"]:checked').val();
        var placeholder = selectedType == '40' ? '输入货值百分比' : '输入所需费用';
        
        var _html = '<td><input type="text" name="line[weight_start][]" placeholder="输入起始值"></td>' +
                    '<td><input type="text" name="line[weight_max][]" placeholder="输入结束值"></td>' +
                    '<td><input type="text" name="line[weight_price][]" placeholder="' + placeholder + '"></td>' +
                    '<td>' +
                    '<button type="button" class="am-btn am-btn-xs am-btn-danger" onclick="freeRuleDelarea(this)">删除</button>' +
                    '</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    // 删除
    function freeRuleDelarea(_this){
        var row = _this.closest('tr');
        row.parentNode.removeChild(row);
    }
    
    $(function () {
        // 表单验证提交
        $('#my-form').superForm({
            // 自定义验证规则
            validate: function() {
                // 验证区间计费规则
                var valid = true;
                $('input[name^="line[weight_start]"]').each(function() {
                    if (!$(this).val()) valid = false;
                });
                if (!valid) {
                    alert('请填写完整的区间计费规则');
                    return false;
                }
                
                // 如果是偏远模式，验证偏远地区设置
                if ($('input[name="line[type]"]:checked').val() == '30') {
                    if (!$('textarea[name="line[remote_areas]"]').val()) {
                        alert('请输入偏远地区列表');
                        return false;
                    }
                }
                return true;
            }
        });
    });
</script>