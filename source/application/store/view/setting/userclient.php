<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">预报功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    单个快递单号预报
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_single]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_single'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_single]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_single'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    多个快递单号预报
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_more]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_more'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_more]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_more'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    快速预报是否填写快递单号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressnum]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_expressnum'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressnum]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_expressnum'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_expressnum_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_expressnum_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    快速预报的单号生成规则
                                </label>
                                <div class="am-u-sm-9">
                                    <select id="selectize-tags-1" onclick="changeorder()" onchange="changeorder()" name="userclient[yubao][orderno][default]" multiple="" class="tag-gradient-success">
                                        <?php if (isset($values['yubao']['orderno']['model']) && isset($values['yubao']['orderno']['default'])): foreach ($values['yubao']['orderno']['default'] as $key =>$item): ?>
                                            <option value="<?= $item ?>" selected ><?= $values['yubao']['orderno']['model'][$item] ?></option>
                                        <?php endforeach; endif; ?>
                                        
                                        <?php if (isset($values['yubao']['orderno']['model']) && isset($values['yubao']['orderno']['default'])): foreach ($values['yubao']['orderno']['model'] as $key =>$items): ?>
                                            <option value="<?= $key ?>" <?= in_array($key,$values['yubao']['orderno']['default'])?"selected":'' ?>><?= $items ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <input id="orderno" autocomplete="off" type="hidden" name="userclient[yubao][orderno][default]"  value="<?= implode(',',$values['yubao']['orderno']['default']) ?>">
                                    <small>注：当快速预报的单号不必填时，则自动按此规则生成；</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 自定义发货单号首字母 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="userclient[yubao][orderno][first_title]"
                                           value="<?= $values['yubao']['orderno']['first_title']??'' ?>" required>
                                            <div class="help-block">
                                        <small>注：当上面发货订单号生成规则选择了首字母才会使用该首字母；</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    快速预报包裹是否直接设置为已入库
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressnum_enter]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_expressnum_enter'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressnum_enter]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_expressnum_enter'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报国家
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_country]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_country'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_country]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_country'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_country_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_country_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报仓库
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_shop]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_shop]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_shop_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_shop_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报快递公司
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressname]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_expressname'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressname]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_expressname'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_expressname_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_expressname_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报类目
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_category]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_category]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_category_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_category_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写唛头
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_userremark]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_userremark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_userremark]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_userremark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_userremark_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_userremark_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品总价格价值
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_price]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_price'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_price]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_price'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_price_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_price_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写备注
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_remark]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_remark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_remark]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_remark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_remark_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_remark_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_images]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_images'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_images]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_images'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_images_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_images_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要确认阅读协议
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_xieyi]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_xieyi'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_xieyi]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_xieyi'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_xieyi_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_xieyi_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品信息
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_goodslist]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_goodslist'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_goodslist]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_goodslist'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <!--<label class="am-checkbox-inline">-->
                                    <!--    <input type="checkbox" name="userclient[yubao][is_goodslist_force]" value="1" data-am-ucheck-->
                                    <!--        <?= $values['yubao']['is_goodslist_force']==1?'checked' : '' ?>>-->
                                    <!--    是否必填-->
                                    <!--</label>-->
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">上门取件功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报国家
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_country]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_country'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_country]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_country'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_country_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_country_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上门取件时间
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_pickup_time]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_pickup_time'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_pickup_time]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_pickup_time'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_pickup_time_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_pickup_time_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报仓库
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_shop]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_shop]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_shop_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_shop_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报类目
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_category]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_category]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_category_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_category_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                      
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品总价值
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_price]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_price'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_price]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_price'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_price_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_price_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写备注
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_remark]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_remark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_remark]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_remark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_remark_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_remark_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_images]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_images'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_images]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_images'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_images_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_images_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要确认阅读协议
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_xieyi]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['is_xieyi'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_xieyi]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['is_xieyi'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[visitdoor][is_xieyi_force]" value="1" data-am-ucheck
                                            <?= $values['visitdoor']['is_xieyi_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group" data-x-switch>
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启打包方式选择
                                </label>
                                  <div class="am-u-sm-9" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_package_type]" value="1"
                                               data-switch-box="switch-package_type"
                                               data-switch-item="package_type__1"
                                               data-am-ucheck  
                                               <?= $values['visitdoor']['is_package_type'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][is_package_type]" value="0"
                                               data-switch-box="switch-package_type"
                                               data-switch-item="package_type__0"
                                               data-am-ucheck <?= $values['visitdoor']['is_package_type'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group switch-package_type package_type__0 <?= $values['visitdoor']['is_package_type'] == 0 ? '' : 'hide' ?>">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    默认打包方式
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][package_type]" value="1"
                                               data-am-ucheck  <?= $values['visitdoor']['package_type'] == 1 ? 'checked' : '' ?>>
                                        直邮
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[visitdoor][package_type]" value="0"
                                               data-am-ucheck <?= $values['visitdoor']['package_type'] == 0 ? 'checked' : '' ?>>
                                        拼邮
                                    </label>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">物品信息设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    条码
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_barcode]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_barcode'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_barcode]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_barcode'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    中文名称
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_goods_name]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_goods_name'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_goods_name]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_goods_name'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_goods_name_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_goods_name_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    日文名称
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_goods_name_en]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_goods_name_en'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_goods_name_en]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_goods_name_en'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_goods_name_en_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_goods_name_en_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    英文名称
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_goods_name_jp]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_goods_name_jp'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_goods_name_jp]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_goods_name_jp'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_goods_name_jp_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_goods_name_jp_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    产品品牌
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_brand]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_brand'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_brand]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_brand'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_brand_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_brand_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    产品规格
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_spec]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_spec'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_spec]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_spec'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_spec_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_spec_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    产品价格
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_price]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_price'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_price]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_price'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_price_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_price_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    毛重
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_gross_weight]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_gross_weight'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_gross_weight]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_gross_weight'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_gross_weight_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_gross_weight_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    净重
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_net_weight]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_net_weight'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_net_weight]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_net_weight'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_net_weight_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_net_weight_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    长宽高
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_depth]" value="1"
                                               data-am-ucheck  <?= $values['goods']['is_depth'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[goods][is_depth]" value="0"
                                               data-am-ucheck <?= $values['goods']['is_depth'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[goods][is_depth_force]" value="1" data-am-ucheck
                                            <?= $values['goods']['is_depth_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
          
                            
           
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户资料功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自定义身份证别名
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="userclient[userinfo][identification_card]"
                                           value="<?= $values['userinfo']['identification_card'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自定义身份证照片别名
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="userclient[userinfo][identification_card_image]"
                                           value="<?= $values['userinfo']['identification_card_image'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传身份证图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_identification_card]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_identification_card'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_identification_card]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_identification_card'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_identification_card_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_identification_card_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写生日
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_birthday]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_birthday'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_birthday]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_birthday'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_birthday_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_birthday_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写微信号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_wechat]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_wechat'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_wechat]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_wechat'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_wechat_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_wechat_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写邮箱
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_email]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_email'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_email]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_email'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_email_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_email_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写手机号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_mobile]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_mobile'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_mobile]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_mobile'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_mobile_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_mobile_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货地址功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启电话前缀
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_tel_code]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_tel_code'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_tel_code]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_tel_code'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启个人通关代码
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_clearancecode]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_clearancecode'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_clearancecode]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_clearancecode'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启身份证号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_identitycard]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_identitycard'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_identitycard]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_identitycard'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启省/州
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_province]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_province'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_province]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_province'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启城市
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_city]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_city'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_city]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_city'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启区
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_region]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_region'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_region]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_region'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启街道
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_street]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_street'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_street]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_street'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启门牌号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_door]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_door'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_door]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_door'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启详细地址
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_detail]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_detail'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_detail]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_detail'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮箱
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_email]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_email'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_email]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_email'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮编
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_code]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_code'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_code]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_code'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启备注
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_remark]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_remark'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_remark]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_remark'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启唛头
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_usermark]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_usermark'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][sendaddress_setting][is_usermark]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['sendaddress_setting']['is_usermark'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">自定义备注</label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[address][sendaddress_setting][remark]"
                                           value="<?= $values['address']['sendaddress_setting']['remark']??'' ?>">
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">收货地址功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启电话前缀
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_tel_code]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_tel_code'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_tel_code]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_tel_code'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启个人通关代码
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_clearancecode]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_clearancecode'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_clearancecode]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_clearancecode'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启身份证号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_identitycard]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_identitycard'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_identitycard]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_identitycard'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启省/州
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_province]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_province'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_province]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_province'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启城市
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_city]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_city'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_city]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_city'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启区
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_region]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_region'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_region]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_region'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启街道
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_street]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_street'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_street]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_street'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启门牌号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_door]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_door'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_door]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_door'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启详细地址
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_detail]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_detail'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_detail]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_detail'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮箱
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_email]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_email'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_email]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_email'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮编
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_code]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_code'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_code]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_code'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启备注
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_remark]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_remark'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_remark]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_remark'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启唛头
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_usermark]" value="1"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_usermark'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[address][reciveaddress_setting][is_usermark]" value="0"
                                               data-am-ucheck
                                            <?= $values['address']['reciveaddress_setting']['is_usermark'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">自定义备注</label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[address][reciveaddress_setting][remark]"
                                           value="<?= $values['address']['reciveaddress_setting']['remark']??'' ?>">
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户打包功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在提交打包前需要完善用户资料
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_force]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_force'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_force]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_force'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在提交打包前可以填写代收款（代收货款）
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_waitreceivedmoney]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_waitreceivedmoney'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_waitreceivedmoney]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_waitreceivedmoney'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    开启自提点
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_packagestation]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_packagestation'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_packagestation]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_packagestation'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>选择不开启后，在用户端提交打包时则无法查看到自提点</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    开启送货上门
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_todoor]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_todoor'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_todoor]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_todoor'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>选择不开启后，在用户端提交打包时则无法选择用户地址</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    打包页面是否展示图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_image]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_image'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_image]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_image'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>选择开启后，在用户端提交打包时就能看到包裹的图片</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户登录功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否强制用户填写地址
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_addressforce]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_addressforce'] == 1 ? 'checked' : '' ?>>
                                        强制
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_addressforce]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_addressforce'] == 0 ? 'checked' : '' ?>>
                                        不强制
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    在小程序或公众号中是否开启账号密码登录方式
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_passwordlogin]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_passwordlogin'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_passwordlogin]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_passwordlogin'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    H5端登录是否展示手机号前缀
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_phone]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_phone'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_phone]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_phone'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>关闭后，H5用户端则不需要选择手机号前缀，如果开启国际短信，请选择开启</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否注册了微信开放平台
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_wxopen]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_wxopen'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_wxopen]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_wxopen'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>开启微信开放平台后可以对微信小程序用户发送模板消息，没有注册则小程序用户只能接收订阅消息<a target="_blank" href="https://open.weixin.qq.com/">微信开放平台注册地址</a></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    新用户注册时，是否合并用户数据，实现多端账户统一（必须先注册微信开放平台）
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_merge_user]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_merge_user'] == 1 ? 'checked' : '' ?>>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_merge_user]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_merge_user'] == 0 ? 'checked' : '' ?>>
                                        否
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>可以实现多种客户端的账号统一，例如H5、微信小程序、APP。如果未开启，则不同端的用户无法合并<a target="_blank" href="https://open.weixin.qq.com/">微信开放平台注册地址</a></small>
                                    </div>
                                </div>
                            </div>
                            <!--运费查询功能设置-->
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">运费查询功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    运费查询后是否展示所有路线
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_line_show]" value="1"
                                               data-am-ucheck
                                            <?= $values['line']['is_line_show'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_line_show]" value="0"
                                               data-am-ucheck
                                            <?= $values['line']['is_line_show'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    运费查询是否关联物品类目
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_categorysearch]" value="1"
                                               data-am-ucheck
                                            <?= $values['line']['is_categorysearch'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_categorysearch]" value="0"
                                               data-am-ucheck
                                            <?= $values['line']['is_categorysearch'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示计费单位
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_chargeunit]" value="1"
                                               data-am-ucheck
                                            <?= $values['line']['is_chargeunit'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_chargeunit]" value="0"
                                               data-am-ucheck
                                            <?= $values['line']['is_chargeunit'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示运输方式
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_shippingfee]" value="1"
                                               data-am-ucheck
                                            <?= $values['line']['is_shippingfee'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_shippingfee]" value="0"
                                               data-am-ucheck
                                            <?= $values['line']['is_shippingfee'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启路线折扣
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_discount]" value="1"
                                               data-am-ucheck
                                            <?= $values['line']['is_discount'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_discount]" value="0"
                                               data-am-ucheck
                                            <?= $values['line']['is_discount'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>路线折扣开启后，所有的用户运费查询，运费计算的运费将在标准价格基础上乘以折扣比例</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    运费查询界面是否开启增值服务
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_service]" value="1"
                                               data-am-ucheck
                                            <?= $values['line']['is_service'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[line][is_service]" value="0"
                                               data-am-ucheck
                                            <?= $values['line']['is_service'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>开启增值服务后，用户选择增值服务，能够查询到更加接近真实运费的结果</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require"> 运费查询排序方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="userclient[line][sort_mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['line']['sort_mode'] == 10 ? 'selected' : '' ?>>按价格排序</option>
                                            <option value="20" <?= $values['line']['sort_mode'] == 20 ? 'selected' : '' ?>>按路线sort排序</option>
                                            <option value="30" <?= $values['line']['sort_mode'] == 30 ? 'selected' : '' ?>>按路线ID自然排序</option>
                                    </select>
                                    <div class="help-block">
                                        <small>目前支持纯数字模式，纯英文模式，数字英文混合模式</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户端下单流程功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第一步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][first_title]"
                                           value="<?= $values['newuserprocess']['first_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第一步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][first_remark]"
                                           value="<?= $values['newuserprocess']['first_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第二步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][second_title]"
                                           value="<?= $values['newuserprocess']['second_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第二步的按钮文字标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][second_anniu]"
                                           value="<?= $values['newuserprocess']['second_anniu'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第二步的按钮跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[newuserprocess][second_tiaozhuantype]" value="1"
                                               data-am-ucheck  <?= $values['newuserprocess']['second_tiaozhuantype'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[newuserprocess][second_tiaozhuantype]" value="2"
                                               data-am-ucheck <?= $values['newuserprocess']['second_tiaozhuantype'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第二步的按钮跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][second_tiaozhuanurl]"
                                           value="<?= $values['newuserprocess']['second_tiaozhuanurl'] ?>">
                                    <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第二步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][second_remark]"
                                           value="<?= $values['newuserprocess']['second_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第三步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][third_title]"
                                           value="<?= $values['newuserprocess']['third_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第三步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][third_remark]"
                                           value="<?= $values['newuserprocess']['third_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第四步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][fourth_title]"
                                           value="<?= $values['newuserprocess']['fourth_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第四步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][fourth_remark]"
                                           value="<?= $values['newuserprocess']['fourth_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户端首页引导区功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否使用原始默认
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][is_default]" value="1"
                                               data-am-ucheck  <?= $values['guide']['is_default'] == 1 ? 'checked' : '' ?>>
                                        默认
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][is_default]" value="0"
                                               data-am-ucheck <?= $values['guide']['is_default'] == 0 ? 'checked' : '' ?>>
                                        自定义
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>使用默认则下方设置不会生效，使用自定义，则请上传完整的图片和跳转路径，否则可能无法跳转</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">第一张图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file1 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['guide']['first_image'])?$values['guide']['first_file_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['guide']['first_file_path'])?$values['guide']['first_file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[guide][first_image]" value="<?=$values['guide']['first_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <div class="help-block am-u-sm-12">
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第一张图跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][first_url_type]" value="1"
                                               data-am-ucheck  <?= $values['guide']['first_url_type'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][first_url_type]" value="2"
                                               data-am-ucheck <?= $values['guide']['first_url_type'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第一张图跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[guide][first_url]"
                                           value="<?= $values['guide']['first_url'] ?>">
                                           <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">第二张图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file2 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['guide']['second_image'])?$values['guide']['second_file_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['guide']['second_file_path'])?$values['guide']['second_file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[guide][second_image]" value="<?=$values['guide']['second_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第二张图跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][second_url_type]" value="1"
                                               data-am-ucheck  <?= $values['guide']['second_url_type'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][second_url_type]" value="2"
                                               data-am-ucheck <?= $values['guide']['second_url_type'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第二张图跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[guide][second_url]"
                                           value="<?= $values['guide']['second_url'] ?>">
                                    <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">第三张图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file3 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['guide']['third_image'])?$values['guide']['third_file_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['guide']['third_file_path'])?$values['guide']['third_file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[guide][third_image]" value="<?=$values['guide']['third_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第三张图跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][third_url_type]" value="1"
                                               data-am-ucheck  <?= $values['guide']['third_url_type'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][third_url_type]" value="2"
                                               data-am-ucheck <?= $values['guide']['third_url_type'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第三张图跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[guide][third_url]"
                                           value="<?= $values['guide']['third_url'] ?>">
                                    <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">引导用户关注公众号</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在首页开启
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[officialaccount][is_index_open]" value="1"
                                               data-am-ucheck  <?= $values['officialaccount']['is_index_open'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[officialaccount][is_index_open]" value="0"
                                               data-am-ucheck <?= $values['officialaccount']['is_index_open'] == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在个人中心开启
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[officialaccount][is_my_open]" value="1"
                                               data-am-ucheck  <?= $values['officialaccount']['is_my_open'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[officialaccount][is_my_open]" value="0"
                                               data-am-ucheck <?= $values['officialaccount']['is_my_open'] == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 公众号名称 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[officialaccount][name]"
                                           value="<?= $values['officialaccount']['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 公众号描述 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[officialaccount][description]"
                                           value="<?= $values['officialaccount']['description'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group" data-x-switch>
                                <label class="am-u-sm-3 am-form-label form-require">
                                    跳转类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" 
                                               value="10" 
                                               name="userclient[officialaccount][type]" 
                                               data-am-ucheck  
                                               <?= $values['officialaccount']['type'] == 10 ? 'checked' : '' ?>
                                               data-switch-box="switch-officialaccount_type"
                                               data-switch-item="officialaccount_type__10"
                                               >
                                        弹出二维码
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[officialaccount][type]" 
                                               value="20"
                                               data-am-ucheck 
                                               <?= $values['officialaccount']['type'] == 20 ? 'checked' : '' ?>
                                               data-switch-box="switch-officialaccount_type"
                                               data-switch-item="officialaccount_type__20"
                                               >
                                        跳转链接
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group switch-officialaccount_type officialaccount_type__20 <?= $values['officialaccount']['type'] == 20 ? '' : 'hide' ?>">
                                <label class="am-u-sm-3 am-form-label"> 引导关注链接 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[officialaccount][link]"
                                           value="<?= $values['officialaccount']['link'] ?>">
                                    <div class="help-block am-u-sm-12">
                                        <small>请使用公众号文章的短链接：如：https://mp.weixin.qq.com/s/NsFDr3-2ixx6P5i_Eeumbg</small>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="am-form-group switch-officialaccount_type officialaccount_type__10 <?= $values['officialaccount']['type'] == 10 ? '' : 'hide' ?>">
                                <label class="am-u-sm-3  am-form-label">公众号二维码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file5 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['officialaccount']['official_pic'])?$values['officialaccount']['official_pic_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['officialaccount']['official_pic_path'])?$values['officialaccount']['official_pic_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[officialaccount][official_pic]" value="<?=$values['officialaccount']['official_pic'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">平台LOGO缩略图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file4 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['officialaccount']['official_image'])?$values['officialaccount']['official_image_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['officialaccount']['official_image_path'])?$values['officialaccount']['official_image_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[officialaccount][official_image]" value="<?=$values['officialaccount']['official_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <div class="help-block am-u-sm-12">
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">底部菜单设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    底部菜单设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][type]" value="type2"
                                               data-am-ucheck  <?= $values['menus']['type'] == 'type2' ? 'checked' : '' ?>>
                                        请从下方预设底部菜单选择
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][type]" value="type1"
                                               data-am-ucheck  <?= $values['menus']['type'] == 'type1' ? 'checked' : '' ?>>
                                        自定义-带快捷方式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][type]" value="type3"
                                               data-am-ucheck <?= $values['menus']['type'] == 'type3' ? 'checked' : '' ?>>
                                        自定义-不带快捷方式
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>选择自定义方式的，请前往【自定义区】【自定义菜单】设置菜单</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    预设底部菜单
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][menu_type]" value="10"
                                               data-am-ucheck
                                            <?= $values['menus']['menu_type'] == '10' ? 'checked' : '' ?>>
                                        A模式:首页/查询/快捷键/拼团/我的
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][menu_type]" value="20"
                                               data-am-ucheck
                                            <?= $values['menus']['menu_type']== '20' ? 'checked' : '' ?>>
                                        B模式:首页/查询/快捷键/运费/我的
                                    </label>
                                    <br>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][menu_type]" value="30"
                                               data-am-ucheck
                                            <?= $values['menus']['menu_type'] == '30' ? 'checked' : '' ?>>
                                        C模式:首页/查询/运费/我的
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][menu_type]" value="40"
                                               data-am-ucheck
                                            <?= $values['menus']['menu_type'] == '40' ? 'checked' : '' ?>>
                                        D模式:首页/查询/运费/拼团/我的
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[menus][menu_type]" value="50"
                                               data-am-ucheck
                                            <?= $values['menus']['menu_type'] == '50' ? 'checked' : '' ?>>
                                        E模式:首页/查询/运费/商城/我的
                                    </label>
                                    <div class="help-block">
                                        <small>注意：默认开启A模式，如需其他模式亲自行设置
                                              <a href="<?= url('store/setting.help/menuSet') ?>" target="_blank">点击查看效果图？</a>
                                        </small>
                                    </div>
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

<link href="/web/static/css/selectize.default.css" rel="stylesheet">
<script src="/web/static/js/selectize.min.js"></script>
<script src="/web/static/js/summernote-bs4.min.js"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
    function changeorder(){
        console.log($('#selectize-tags-1')[0]);
        $('#orderno').val($('#selectize-tags-1')[0].selectize.items);
    }
    $(function () {
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
         // 选择图片
        $('.upload-file1').selectImages({
            name: 'userclient[guide][first_image]'
        });
        $('.upload-file2').selectImages({
            name: 'userclient[guide][second_image]'
        });
        $('.upload-file3').selectImages({
            name: 'userclient[guide][third_image]'
        });
        
        $('.upload-file4').selectImages({
            name: 'userclient[officialaccount][official_image]'
        });
        
        $('.upload-file5').selectImages({
            name: 'userclient[officialaccount][official_pic]'
        });
        
        // swith切换
        var $mySwitch = $('[data-x-switch]');
        $mySwitch.find('[data-switch-item]').click(function () {
            var $mySwitchBox = $('.' + $(this).data('switch-box'));
            $mySwitchBox.hide().filter('.' + $(this).data('switch-item')).show();
        });
    });
</script>
