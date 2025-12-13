<style>
   .am-form-up { display: none;}
   .am-form-title { font-size: 13px; color:#666; cursor: pointer; }
   .am-form-up-item { width: 100%; height: auto;} 
   .am-form-item-del { font-size: 13px; color: #ff6666; cursor: pointer;}
   .support-title { font-size:13px; height:30px; line-height:30px; color:#ccc;}
   .support-list { display:block;}
</style> 
<?php $weiStatus=[10=>'g',20=>'kg',30=>'bl',40=>'CBM'] ?>
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"><?= isset($model) ? '编辑' : '新增' ?>线路地址</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[name]" value="<?= $model['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 运输形式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php if (count($linecategory)>0): foreach ($linecategory as $key =>$item): ?>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_category]"  value="<?= $item['category_id'] ?>" data-am-ucheck  <?= $item['category_id']==$model['line_category']?'checked':'' ?>>
                                        <?= $item['name'] ?>
                                    </label>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 支持会员等级 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="line[grade_id]" class="am-form-field" data-am-selected="{searchBox: 1, maxHeight: 200}">
                                        <option value="-1" <?= (!isset($model['grade_id']) || $model['grade_id'] == -1) ? 'selected' : '' ?>>所有人都适用</option>
                                        <option value="0" <?= (isset($model['grade_id']) && $model['grade_id'] == 0) ? 'selected' : '' ?>>仅普货会员适用</option>
                                        <?php if (count($gradeList)>0): foreach ($gradeList as $grade): ?>
                                        <option value="<?= $grade['grade_id'] ?>" <?= (isset($model['grade_id']) && $model['grade_id'] == $grade['grade_id']) ? 'selected' : '' ?>><?= $grade['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block"><small>选择该线路适用的会员等级：-1表示所有人都适用，0表示仅普货会员适用，>0表示针对对应会员等级</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">线路图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= $model['image']['file_path'] ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= $model['image']['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="nav[image_id]"
                                                           value="<?= $model['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路模式 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_type]" onclick="switchLineMode(this)"  <?= $model['line_type'] == 0 ? 'checked' : '' ?> value="0" data-am-ucheck
                                               checked>
                                        按重量
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_type]" onclick="switchLineMode(this)"  <?= $model['line_type'] == 1 ? 'checked' : '' ?> value="1" data-am-ucheck>
                                         按体积
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 重量向上取整 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[weight_integer]" <?= $model['weight_integer'] == 1 ? 'checked' : '' ?> value="1" data-am-ucheck
                                               checked>
                                        向上取整
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[weight_integer]"  <?= $model['weight_integer'] == 0 ? 'checked' : '' ?> value="0" data-am-ucheck>
                                        按实际重量
                                    </label>
                                    <div class="help-block"><small>当包裹重量在参与计算前，当重量为6.2时候，按7计算。续重重量则为(7-1)/0.5=12，如此算下来的费用则为100+12*10=220元</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重向上取整 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[weightvol_integer]" <?= $model['weightvol_integer'] == 1 ? 'checked' : '' ?> value="1" data-am-ucheck
                                               checked>
                                        向上取整
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[weightvol_integer]"  <?= $model['weightvol_integer'] == 0 ? 'checked' : '' ?> value="0" data-am-ucheck>
                                        按实际重量
                                    </label>
                                    <div class="help-block"><small>当根据长*宽*高/体积重系数，计算体积重时，是否将得到的体积重结果向上取整，比如20*30*9.9/6000=9.9,如向上取整则为10kg，如不向上取整则按9.9跟实际重量比较大小</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 续重是否向上取整 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[is_integer]" <?= $model['is_integer'] == 1 ? 'checked' : '' ?> value="1" data-am-ucheck
                                               checked>
                                        向上取整
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[is_integer]"  <?= $model['is_integer'] == 2 ? 'checked' : '' ?> value="2" data-am-ucheck>
                                        按实际重量
                                    </label>
                                    <div class="help-block"><small>当包裹重量在参与计算时，如首重1kg100元，续重10元/0.5kg，当重量为6.2时候，续重重量则为(6.2-1)/0.5=10.4，向上取整则为11，如此算下来的费用则为100+11*10=210元</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 多个箱子/订单时体积重和实重取值 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[billing_method]"  <?= $model['billing_method'] == 10 ? 'checked' : '' ?> value="10" data-am-ucheck
                                               checked>
                                        A:按单个箱子的体积重和实重比大小
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[billing_method]"  <?= $model['billing_method'] == 20 ? 'checked' : '' ?> value="20" data-am-ucheck>
                                        B:按汇总的体积重跟实重比大小
                                    </label>
                                    <div class="help-block"><small>当一个订单有多个箱子，比如箱子1体积重=5.4，实重=4；箱子2体积重为5.6，实重为6.如果按A则取每个箱子的最大值，计费重量=5.4+6=11.4。如果按B则体积重之和=5.4+5.6=11，实重之和=4+6=10，取大值后计费重量就是11</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路重量单位 </label>
                                <div class="am-u-sm-9 am-u-end">
                            
                                    <label class="am-radio-inline line_type_weight" <?= $model['line_type'] == 0 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 10 ? 'checked' : '' ?> value="10" data-am-ucheck>
                                        g
                                    </label>
                                    <label class="am-radio-inline line_type_weight" <?= $model['line_type'] == 0 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 20 ? 'checked' : '' ?> value="20" data-am-ucheck>
                                         kg
                                    </label>
                                    <label class="am-radio-inline line_type_weight" <?= $model['line_type'] == 0 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 30 ? 'checked' : '' ?> value="30" data-am-ucheck>
                                         lbs
                                    </label>
                                 
                                    <label class="am-radio-inline line_type_vol" <?= $model['line_type'] == 1 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 40 ? 'checked' : '' ?> value="40" data-am-ucheck>
                                         CBM
                                    </label>
                            
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重泡货比 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 5000 ? 'checked' : '' ?> value="5000" data-am-ucheck
                                               checked>
                                        5000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 6000 ? 'checked' : '' ?> value="6000" data-am-ucheck>
                                         6000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 7000 ? 'checked' : '' ?> value="7000" data-am-ucheck>
                                         7000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 8000 ? 'checked' : '' ?> value="8000" data-am-ucheck>
                                         8000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 9000 ? 'checked' : '' ?> value="9000" data-am-ucheck>
                                         9000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 10000 ? 'checked' : '' ?> value="10000" data-am-ucheck>
                                        10000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 15000 ? 'checked' : '' ?> value="15000" data-am-ucheck>
                                        15000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 18000 ? 'checked' : '' ?> value="18000" data-am-ucheck>
                                        18000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 27000 ? 'checked' : '' ?> value="27000" data-am-ucheck>
                                        27000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 28316 ? 'checked' : '' ?> value="28316" data-am-ucheck>
                                        28316计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 1000000 ? 'checked' : '' ?> value="1000000" data-am-ucheck>
                                        1000000(百万)
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 166 ? 'checked' : '' ?> value="166" data-am-ucheck>
                                         166计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 139 ? 'checked' : '' ?> value="139" data-am-ucheck>
                                         139计费
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重大于实重  </label>
                                <div class="am-u-sm-1 am-u-end">
                                    <input  min="1" type="number" class="tpl-form-input" name="line[volumeweight_weight]" value="<?= $model['volumeweight_weight'] ?>" required>
                                </div>
                               <span style="font-size: 16px;">倍时以体积重计费</span>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重计算模式 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight_type]"  <?= $model['volumeweight_type'] == 10 ? 'checked' : '' ?> value="10" data-am-ucheck
                                               checked>
                                        长*宽*高/体积重泡货比
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight_type]"   <?= $model['volumeweight_type'] == 20 ? 'checked' : '' ?> value="20" data-am-ucheck>
                                        实重+（长*宽*高/6000-实重）*70%(选择此模式请设置下方的百分比)
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 泡货比 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[bubble_weight]" value="<?= $model['bubble_weight'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 计费模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]"  value="1"  onclick="switchMode(this)" <?= $model['free_mode'] == 1 ? 'checked' : '' ?> data-am-ucheck
                                               checked>
                                        阶梯计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" <?= $model['free_mode'] == 2 ? 'checked' : '' ?> value="2" data-am-ucheck>
                                        首/续重模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input value="3" type="radio" name="line[free_mode]" onclick="switchMode(this)" <?= $model['free_mode'] == 3 ? 'checked' : '' ?> data-am-ucheck>
                                        范围区间计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input value="4" type="radio" name="line[free_mode]" onclick="switchMode(this)" <?= $model['free_mode'] == 4 ? 'checked' : '' ?> data-am-ucheck>
                                        重量区间计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" value="5" <?= $model['free_mode'] == 5 ? 'checked' : '' ?> data-am-ucheck>
                                        混合计费模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" value="6" <?= $model['free_mode'] == 6 ? 'checked' : '' ?> data-am-ucheck>
                                        阶梯首续重规则
                                    </label>
                                    <div class="help-block"><small>范围区间计费,举例说明:1-10kg,价格20元,是指不管是1kg还是10kg,总价格就是20元.重量区间计费,举例说明:1-10kg,价格20元,是指在1-10kg之间时,每kg费用20元,当重量为5kg时,总价格为5 * 20 = 100元</small></div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 计费规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <!--阶梯计费模式--->
                                     <div class="am-form-up" id="step_mode"  <?= $model['free_mode'] == 1 ? 'style="display: block;"' : '' ?>>
                                             <div class="am-form-title" style="height:40px; line-height:35px;">阶梯计费规则 
                                                  <span style="color:#ff6600;" onclick="addfreeRule(this)">新增计费规则</span>
                                             </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>最大限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="step_mode">
                                                    <tr>
                                                       <?php if (isset($model['free_rule']) && $model['free_mode'] == 1): ?> 
                                                        <?php foreach($model['free_rule'] as $item2): ?>
                                                        <tr>
                                                        <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-email-1" placeholder="输入起始重量" value="<?= $item2['weight'][0]; ?>"></td>
                                                        <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-email-1" placeholder="输入结束重量" value="<?= $item2['weight'][1]??''; ?>"></td>
                                                        <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-email-1" placeholder="输入所需价格" value="<?= $item2['weight_price'] ; ?>"></td>
                                                        <td onclick="freeRuleDel(this)">删除</td>
                                                    </tr>
                                                         <?php endforeach;?>
                                                       <?php endif; ?>
                                                    </tr>
                                                </tbody>
                                               </table>
                                     </div>
                                      <!--首/续重计费模式--->
                                     <div class="am-form-up" id="format_mode" <?= $model['free_mode'] == 2 ? 'style="display: block;"' : '' ?>>
                                          <div class="am-form-title" style="height:40px; line-height:35px;"> 首/续重模式</div>
                                          
                                          <?php if ($model['free_mode'] == 2 && isset($model['free_rule'][0]['first_weight'])): ?> 
                                          <?php foreach($model['free_rule'] as $item3): ?>
                                          <div class="am-form-group" >
                                                <label class="am-u-lg-2 am-form-label"> 首重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_weight]" value="<?= isset($item3['first_weight'])?$item3['first_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 首重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_price]" value="<?= isset($item3['first_price'])?$item3['first_price']:'' ; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_weight]" value="<?= isset($item3['first_price'])?$item3['next_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_price]" value="<?= isset($item3['first_price'])?$item3['next_price']:''; ?>"
                                                           required>
                                                </div>
                                            </div>
                                          <?php endforeach;?>
                                          <?php else : ?>
                                           <div class="am-form-group" >
                                                <label class="am-u-lg-2 am-form-label"> 首重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_weight]" value="<?= isset($item3['first_weight'])?$item3['first_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 首重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_price]" value="<?= isset($item3['first_price'])?$item3['first_price']:'' ; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_weight]" value="<?= isset($item3['first_price'])?$item3['next_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_price]" value="<?= isset($item3['first_price'])?$item3['next_price']:''; ?>"
                                                           required>
                                                </div>
                                                
                                            </div>
                                        <?php endif; ?>
                                          
                                     </div>
                                     
                                     <!--区间计费规则--->
                                     <div class="am-form-up" id="area_mode"  <?= in_array($model['free_mode'],[3]) ? 'style="display: block;"' : '' ?>>
                                             <div class="am-form-title" style="height:40px; line-height:35px;">范围区间计费 
                                                  <span style="color:#ff6600;" onclick="addfreeRulearea(this)">新增计费规则</span>
                                             </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>最大限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="area_mode">
                                                    <tr>
                                                       <?php if (isset($model['free_rule']) && in_array($model['free_mode'],[3])): ?> 
                                                        <?php foreach($model['free_rule'] as $item4): ?>
                                                        <tr>
                                                        <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item4['weight'][0]; ?>"></td>
                                                        <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item4['weight'][1]??''; ?>"></td>
                                                        <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需价格" value="<?= $item4['weight_price'] ; ?>"></td>
                                                        <td><input type="hidden" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="1"></td>
                                                        <td onclick="freeRuleDelarea(this)">删除</td>
                                                    </tr>
                                                         <?php endforeach;?>
                                                       <?php endif; ?>
                                                    </tr>
                                                </tbody>
                                               </table>
                                     </div>
                                     
                                       <!--区间计费规则--->
                                     <div class="am-form-up" id="area_mode_unit"  <?= in_array($model['free_mode'],[4]) ? 'style="display: block;"' : '' ?>>
                                             <div class="am-form-title" style="height:40px; line-height:35px;">重量区间计费 
                                                  <span style="color:#ff6600;" onclick="addfreeRuleareaunit(this)">新增计费规则</span>
                                             </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>最大限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                        <th>计费单位重量(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="area_mode_unit">
                                                    <tr>
                                                       <?php if (isset($model['free_rule']) && in_array($model['free_mode'],[4])): ?> 
                                                        <?php foreach($model['free_rule'] as $item4): ?>
                                                        <tr>
                                                            <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item4['weight'][0]; ?>"></td>
                                                            <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item4['weight'][1]??''; ?>"></td>
                                                            <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需价格" value="<?= $item4['weight_price'] ; ?>"></td>
                                                            <td><input type="text" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="<?= $item4['weight_unit'] ; ?>"></td>
                                                            <td onclick="freeRuleDelareaunit(this)">删除</td>
                                                        </tr>
                                                         <?php endforeach;?>
                                                       <?php endif; ?>
                                                    </tr>
                                                </tbody>
                                               </table>
                                     </div>
                                     
                                       <div class="am-form-up" id="hunhe_mode_unit" <?= in_array($model['free_mode'],[5]) ? 'style="display: block;"' : '' ?>>
                                          <div class="am-form-title" style="height:40px; line-height:35px;">混合计费规则
                                          <span style="color:#ff6600;" onclick="addshouxuzhong(this)">新增首续重计费</span>
                                          <span style="color:#ff6600;" onclick="addqujian(this)">新增区间计费</span>
                                          <span style="color:#ff6600;" onclick="addweightqujian(this)">新增重量区间计费</span>
                                          </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['price_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="hunhe_mode_unit">
                                                    <?php if (isset($model['free_rule']) && in_array($model['free_mode'],[5])): ?> 
                                                    <?php foreach($model['free_rule'] as $item5): ?>
                                                    <?php if ($item5['type']==1): ?> 
                                                    <tr>
                                                        <input type="hidden" class="tpl-form-input" name="line[5][type]" value="1" placeholder="首续重"  required>
                                                        <td>起始重量<input type="text" name="line[5][weight_start]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item5['weight'][0]; ?>"></td>
                                                        <td>结束重量<input type="text" name="line[5][weight_max]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item5['weight'][1]??''; ?>"></td>
                                                        <td>首重<input type="number" min="0" class="tpl-form-input" name="line[5][first_weight]" value="<?= $item5['first_weight']; ?>" placeholder="输入首重"  required></td>
                                                        <td>首重费用<input type="number" min="0" class="tpl-form-input" name="line[5][first_price]" value="<?= $item5['first_price']; ?>" placeholder="输入首重费用"  required></td>
                                                        <td>续重<input type="number" min="0" class="tpl-form-input" name="line[5][next_weight]" value="<?= $item5['next_weight']; ?>" placeholder="输入续重"  required></td>
                                                        <td>续重费用<input type="number" min="0" class="tpl-form-input" name="line[5][next_price]" value="<?= $item5['next_price']; ?>" placeholder="输入续重费用"  required></td>
                                                        <td onclick="deleteshouxuzhong(this)">删除</td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if ($item5['type']==2): ?> 
                                                    <tr>
                                                        <input type="hidden" class="tpl-form-input" name="line[6][type]" value="2" placeholder="首续重"  required>
                                                        <td><input type="text" name="line[6][weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item5['weight'][0]; ?>"></td>
                                                        <td><input type="text" name="line[6][weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item5['weight'][1]??''; ?>"></td>
                                                        <td><input type="text" name="line[6][weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需价格" value="<?= $item5['weight_price'] ; ?>"></td>
                                                        <td><input type="hidden" name="line[6][weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="1"></td>
                                                        <td onclick="deletequjian(this)">删除</td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if ($item5['type']==3): ?> 
                                                    <tr>
                                                            <input type="hidden" class="tpl-form-input" name="line[7][type]" value="3" placeholder="首续重"  required>
                                                            <td><input type="text" name="line[7][weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item5['weight'][0]; ?>"></td>
                                                            <td><input type="text" name="line[7][weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item5['weight'][1]??''; ?>"></td>
                                                            <td><input type="text" name="line[7][weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需价格" value="<?= $item5['weight_price'] ; ?>"></td>
                                                            <td><input type="text" name="line[7][weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="<?= $item5['weight_unit'] ; ?>"></td>
                                                            <td onclick="deleteweightqujian(this)">删除</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    
                                                    <?php endforeach;?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                         </div>
                                         
                                         <!--阶梯首续重计费-->
                                         <div class="am-form-up" id="jieti_mode_unit" <?= in_array($model['free_mode'],[6]) ? 'style="display: block;"' : '' ?>>
                                          <div class="am-form-title" style="height:40px; line-height:35px;">阶梯首续重规则 
                                          <span style="color:#ff6600;" onclick="addqujianshouxuzhong(this)">新增阶梯首续重</span>
                                          </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr><th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['price_mode']['unit'] ?>)</th>
                                                        <th>单位(<?= $set['weight_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="jieti_mode_unit">
                                                    <?php if (isset($model['free_rule']) && in_array($model['free_mode'],[6])): ?> 
                                                    <?php foreach($model['free_rule'] as $item5): ?>
                             
                                                    <tr>
                                                        <input type="hidden" class="tpl-form-input" name="line[8][type]" value="1" placeholder="首续重"  required>
                                                        <td>起始重量<input type="text" name="line[8][weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item5['weight'][0]; ?>"></td>
                                                        <td>结束重量<input type="text" name="line[8][weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item5['weight'][1]??''; ?>"></td>
                                                        <td>首重<input type="number" min="0" class="tpl-form-input" name="line[8][first_weight][]" value="<?= $item5['first_weight']; ?>" placeholder="输入首重"  required></td>
                                                        <td>首重费用<input type="number" min="0" class="tpl-form-input" name="line[8][first_price][]" value="<?= $item5['first_price']; ?>" placeholder="输入首重费用"  required></td>
                                                        <td>续重<input type="number" min="0" class="tpl-form-input" name="line[8][next_weight][]" value="<?= $item5['next_weight']; ?>" placeholder="输入续重"  required></td>
                                                        <td>续重费用<input type="number" min="0" class="tpl-form-input" name="line[8][next_price][]" value="<?= $item5['next_price']; ?>" placeholder="输入续重费用"  required></td>
                                                        <td onclick="deleteshouxufei(this)">删除</td>
                                                    </tr>
                                                    <?php endforeach;?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                         </div>
                                         
                                    
                                </div>
                            </div>
                            
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 限制最少重量 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[weight_min]" value="<?= $model['weight_min']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">限制最大重量 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[max_weight]" value="<?= $model['max_weight']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 重量限制说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <textarea name="line[weight_limit]" id="" class="tpl_form_input" cols="30" rows="5"><?= $model['weight_limit'];?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 运送时效 (<?= $set['price_mode']['unit'] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text" min="0" class="tpl-form-input" name="line[limitationofdelivery]" value="<?= $model['limitationofdelivery']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 超时时间</label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[exceed_date]" value="<?= $model['exceed_date']; ?>"
                                           required><small>设置订单超时的最大时间，在订单发货累计到达设置天数后，则订单会被标记为超时件。设置0天则不参与超时处理</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 关税说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text"  class="tpl-form-input" name="line[tariff]" value="<?= $model['tariff']; ?>"
                                           required>
                                </div>
                            </div>
                           
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">
                                    增值服务
                                </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="selectize-tags-1" onclick="changeorder()" onchange="changeorder()" name="line[services_require]" multiple="" class="tag-gradient-success">
                                        <?php if (count($lineservice)>0): foreach ($lineservice as $key =>$item): ?>
                                            <option value="<?= $item['service_id'] ?>" <?= in_array($item['service_id'],explode(',',$model['services_require']))?"selected":'' ?>><?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <input id="orderno" autocomplete="off" type="hidden" name="line[services_require]"  value="<?= $model['services_require']?>">
                                    <small>注：不需要增值服务可不选；</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 增值服务说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text"  class="tpl-form-input" name="line[service_route]" value="<?= $model['service_route']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 线路特点 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" min="0" class="tpl-form-input" name="line[line_special]" value="<?= $model['line_special'];?>"
                                           >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 物品限制 </label>
                                <div class="am-u-sm-9 am-u-end">
                                      <textarea name="line[goods_limit]" id="" class="tpl_form_input" cols="30" rows="5"><?= $model['goods_limit'];?></textarea>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 体积限制说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <textarea name="line[length_limit]" id="" class="tpl_form_input" cols="30" rows="5"><?= $model['length_limit'];?></textarea>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="line[sort]" value="<?= $model['sort'];?>"
                                           required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 国家支持 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="am-u-sm-9">
                                          <div class="support-title">共支持 <span><?= count($model['country']) ;?></span> 个</div>
                                          <input name="line[countrys]" id="countrys_id" type="hidden" value="<?= $model['countrys'] ;?>">
                                          <div class="support-list" id="support-list">
                                              <?php foreach($model['country'] as $v):?>
                                                  <button type="button" data-id='<?= $v['id']; ?>' class="am-btn am-btn-default am-btn-xs country"><?= $v['title']; ?></button>
                                              <?php endforeach; ?>
                                          </div>
                                     </div>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm support-add">重新选择</button>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm support-app">追加国家</button>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 类目支持 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="am-u-sm-9">
                                          <div class="category-title">共支持 <span><?= count($model['category']) ;?></span> 个</div>
                                          <input name="line[categorys]" id="category_id" type="hidden" value="<?= $model['categorys'] ;?>">
                                          <div class="category-list" id="category-list">
                                              <?php foreach($model['category'] as $v):?>
                                                  <button type="button" data-id='<?= $v['category_id']; ?>' class="am-btn am-btn-default am-btn-xs category"><?= $v['name']; ?></button>
                                              <?php endforeach; ?>
                                          </div>
                                     </div>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm category-add">重新类目</button>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm category-app">追加类目</button>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属仓库 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="line[shop_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'所有仓库', maxHeight: 400}">
                                            <option value="0">所有仓库</option>
                                        <?php if (isset($shopList)): foreach ($shopList as $role): ?>
                                            <option value="<?= $role['shop_id'] ?>"
                                                <?= $role['shop_id']==$model['shop_id']? 'selected' : '' ?>>
                                                <?= $role['shop_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>可以设置多个仓库，让管理员能够查看多个仓库的包裹和订单信息</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="1" data-am-ucheck
                                                <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="0" data-am-ucheck
                                         <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 推荐至首页 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[is_recommend]" value="1" data-am-ucheck
                                                <?= $model['is_recommend'] == 1 ? 'checked' : '' ?>>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[is_recommend]" value="2" data-am-ucheck
                                         <?= $model['is_recommend'] == 2 ? 'checked' : '' ?>>
                                        否
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">更多规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="line[line_content]"
                                              type="text/plain"><?= $model['line_content'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<link href="/web/static/css/selectize.default.css" rel="stylesheet">
<script src="/web/static/js/summernote-bs4.min.js"></script>
<script src="/web/static/js/selectize.min.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script id="tpl-country-item" type="text/template">
    {{ each data as value }}
      <button type="button" data-id='{{value.id}}' class="am-btn am-btn-default am-btn-xs country">{{value.title}}</button>
    {{ /each }}
</script>
<script id="tpl-category-item" type="text/template">
    {{ each data as value }}
      <button type="button" data-id='{{value.id}}' class="am-btn am-btn-default am-btn-xs category">{{value.name}}</button>
    {{ /each }}
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    function changeorder(){
        console.log($('#selectize-tags-1')[0]);
        $('#orderno').val($('#selectize-tags-1')[0].selectize.items);
    }
     // 选择国家
    $('.support-add').click(function () {
        var $countryList = $('.support-list');
        $.selectData({
            title: '选择国家',
            uri: 'Country/countryList',
            dataIndex: 'id',
            done: function (list) {
                var data = {};
                var select_ids = [];
                data['data'] = list;
                console.log(data['data']);
                for (var i=0; i<data['data'].length; i++){
                      select_ids.push(data['data'][i].id);
                    }
                $('#countrys_id').val(select_ids.join(','));
                $countryList.html(template('tpl-country-item', data));
            }
        });
    });
    
    
     // 选择类目
    $('.category-add').click(function () {
        var $countryList = $('.category-list');
        $.selectData({
            title: '选择类目',
            uri: 'Category/categoryList',
            dataIndex: 'id',
            done: function (list) {
                var data = {};
                var select_ids = [];
                data['data'] = list;
                console.log(data['data']);
                for (var i=0; i<data['data'].length; i++){
                      select_ids.push(data['data'][i].id);
                    }
                $('#category_id').val(select_ids.join(','));
                $countryList.html(template('tpl-category-item', data));
            }
        });
    });
    
     // 追加国家
    $('.support-app').click(function () {
        var $countryList = $('.support-list');
        $.selectData({
            title: '选择国家',
            uri: 'Country/countryList',
            dataIndex: 'id',
            done: function (list) {
                var data = {};
                var select_ids = [];
                data['data'] = list;
                console.log(data['data']);
                for (var i=0; i<data['data'].length; i++){
                      select_ids.push(data['data'][i].id);
                    }
                if($('#countrys_id').val()){
                $('#countrys_id').val($('#countrys_id').val()+","+select_ids.join(','));}else{
                     $('#countrys_id').val(select_ids.join(','));
                }
                $countryList.append(template('tpl-country-item', data));
            }
        });
    });
    
     // 追加类目
    $('.category-app').click(function () {
        var $categoryList = $('.category-list');
        $.selectData({
            title: '选择类目',
            uri: 'Category/categoryList',
            dataIndex: 'id',
            done: function (list) {
                var data = {};
                var select_ids = [];
                data['data'] = list;
                console.log(data['data']);
                for (var i=0; i<data['data'].length; i++){
                      select_ids.push(data['data'][i].id);
                    }
                if($('#category_id').val()){
                $('#category_id').val($('#category_id').val()+","+select_ids.join(','));}else{
                     $('#category_id').val(select_ids.join(','));
                }
                $categoryList.append(template('tpl-category-item', data));
            }
        });
    });

    // 富文本编辑器
    UM.getEditor('container', {
        initialFrameWidth: 375 + 15,
        initialFrameHeight: 600
    });
    // 切换计费模式
    function switchMode(_this){
        var _mode = _this.value;
        $('.am-form-up').css('display','none');
        if(_mode==1){
            var freeMode = '#step_mode';
        }
        if(_mode==2){
            var freeMode = '#format_mode';
        }
        if(_mode==3){
            var freeMode = '#area_mode';
        }
        if(_mode==4){
            var freeMode = '#area_mode_unit';
        }
        if(_mode==5){
            var freeMode = '#hunhe_mode_unit';
        }
        if(_mode==6){
            var freeMode = '#jieti_mode_unit';
        }
        $(freeMode).css('display','block');
    }
    

    function switchLineMode(_this){
        var _mode = _this.value;
        $('.line_type_weight').css('display','none');
        $('.line_type_vol').css('display','none');
        if(_mode==0){
            var freeMode = '.line_type_weight';
        }
        if(_mode==1){
            var freeMode = '.line_type_vol';
        }
        $(freeMode).css('display','inline-block');
    }

    function addfreeRule(){
        var amformItem = document.getElementsByClassName('step_mode')[0];
        var Item = document.createElement('tr');
      
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-email-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-email-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-email-1"placeholder="输入所需价格"></td><td class="" onclick="freeRuleDel(this)">删除</td>';
        Item.innerHTML = _html;
        amformItem.appendChild(Item);
    }

    // 删除
    function freeRuleDel(_this){
       var amformItem = document.getElementsByClassName('step_mode')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
    function addfreeRulearea(){
        var amformItem = document.getElementsByClassName('area_mode')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input value="1" type="hidden"name="line[weight_unit][]" class=""id="doc-ipt-unit-1" placeholder="输入单位重量(如：0.5或1)"></td><td class="" onclick="freeRuleDelarea(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    function addfreeRuleareaunit(){
        var amformItem = document.getElementsByClassName('area_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input type="text"name="line[weight_unit][]"class=""id="doc-ipt-unit-1"placeholder="输入单位重量(如：0.5或1)"></td><td class="" onclick="freeRuleDelareaunit(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    function addqujian(){
        var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="hidden" class="tpl-form-input" name="line[6][type]" value="2" placeholder="范围区间"  required><input type="text"name="line[6][weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[6][weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[6][weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input type="hidden" name="line[6][weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="1"><td class="" onclick="deletequjian(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    
    function addshouxuzhong(){
        var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<input type="hidden" class="tpl-form-input" name="line[5][type]" value="1" placeholder="首续重"  required><td>起始重量<input type="text" name="line[5][weight_start]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value=""></td><td>结束重量<input type="text" name="line[5][weight_max]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value=""></td><td>首重<input type="number" min="0" class="tpl-form-input" name="line[5][first_weight]" value="" placeholder="输入首重"  required></td><td>首重费用<input type="number" min="0" class="tpl-form-input" name="line[5][first_price]" value="" placeholder="输入首重费用"  required></td><td>续重<input type="number" min="0" class="tpl-form-input" name="line[5][next_weight]" value="" placeholder="输入续重"  required></td><td>续重费用<input type="number" min="0" class="tpl-form-input" name="line[5][next_price]" value="" placeholder="输入续重费用"  required></td><td onclick="deleteshouxuzhong(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    
        // 删除
    function deleteshouxuzhong(_this){
       var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }

    
    
        // 删除
    function deletequjian(_this){
       var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }


    function addweightqujian(){
        var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="hidden" class="tpl-form-input" name="line[7][type]" value="3" placeholder="重量区间"  required><input type="text"name="line[7][weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[7][weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[7][weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input type="text" name="line[7][weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量"></td><td class="" onclick="deleteweightqujian(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    
    function deleteweightqujian(_this){
       var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }

    // 删除
    function freeRuleDelarea(_this){
       var amformItem = document.getElementsByClassName('area_mode')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
     // 删除
    function deleteshouxufei(_this){
       var amformItem = document.getElementsByClassName('hunhe_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
    // 删除
    function freeRuleDelareaunit(_this){
       var amformItem = document.getElementsByClassName('area_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
        function addqujianshouxuzhong(){
        var amformItem = document.getElementsByClassName('jieti_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<input type="hidden" class="tpl-form-input" name="line[8][type]" value="1" placeholder="首续重"  required><td>起始重量<input type="text"name="line[8][weight_start][]"class=""id="doc-ipt-email-1"placeholder="输入起始重量"></td><td>结束重量<input type="text"name="line[8][weight_max][]"class=""id="doc-ipt-email-1"placeholder="输入结束重量"></td><td>首重<input type="number" min="0" class="tpl-form-input" name="line[8][first_weight][]" value="" placeholder="输入首重"  required></td><td>首重费用<input type="number" min="0" class="tpl-form-input" name="line[8][first_price][]" value="" placeholder="输入首重费用"  required></td><td>续重<input type="number" min="0" class="tpl-form-input" name="line[8][next_weight][]" value="" placeholder="输入续重"  required></td><td>续重费用<input type="number" min="0" class="tpl-form-input" name="line[8][next_price][]" value="" placeholder="输入续重费用"  required></td><td onclick="deletequjianshouxuzhong(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    function deletequjianshouxuzhong(_this){
       var amformItem = document.getElementsByClassName('jieti_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }

    $(function () {
        // 选择图片
        $('.upload-file').selectImages({
            name: 'line[image_id]'
        });
        
        $('#selectize-tags-1').selectize({
    	    delimiter: ',',
    	    persist: false,
    	    create: function(input) {
    	        return {
    	            value: input,
    	            text: input
    	        }
    	    }
	    });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    })
</script>
<script>
    $(function(){
       checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('body').getElementsByTagName('input');
              this.num = this.check.length;
              this.bindEvent();
          },
          bindEvent:function(){
              var that = this;
              for(var i=0; i< this.check.length; i++){
                  this.check[i].onclick = function(){
                       var _check = that.isFullCheck();
                       if (_check){
                           document.getElementById('checkAll').checked = 'checked';
                       }else{
                           document.getElementById('checkAll').checked = '';
                       }
                  }
              }
              var  allCheck = document.getElementById('checkAll');
              allCheck.onclick = function(){
                  if (this.checked){
                      that.setFullCheck();
                  }else{
                      that.setFullCheck('');
                  }
              }
              
          },
          setFullCheck:function(checked='checked'){
             for (var ik =0; ik<this.num; ik++){
                  this.check[ik].checked = checked; 
              } 
          },
          isFullCheck:function(){
              var hasCheck = 0;
              for (var k =0; k<this.num; k++){
                   if (this.check[k].checked){
                       hasCheck++;
                   }
              }
              return hasCheck==this.num?true:false;
          },
          getCheckSelect:function(){
              var selectIds = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectIds.push(this.check[i].value);
                    }
              }
              return selectIds;
          }
       }
    });
</script>
