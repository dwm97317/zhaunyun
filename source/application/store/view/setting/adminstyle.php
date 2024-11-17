<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">电脑端全局功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    集运订单分页默认数量设置
                                </label>
                                <div class="am-u-sm-9">
                                   <select name="adminstyle[pageno][inpack]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="15" <?= $values['pageno']['inpack'] == '15' ? 'selected' : '' ?>>15</option>
                                            <option value="30" <?= $values['pageno']['inpack'] == '30' ? 'selected' : '' ?>>30</option>
                                            <option value="50" <?= $values['pageno']['inpack'] == '50' ? 'selected' : '' ?>>50</option>
                                            <option value="100" <?= $values['pageno']['inpack'] == '100' ? 'selected' : '' ?>>100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    包裹分页默认数量设置
                                </label>
                                <div class="am-u-sm-9">
                                   <select name="adminstyle[pageno][package]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="15" <?= $values['pageno']['package'] == '15' ? 'selected' : '' ?>>15</option>
                                            <option value="30" <?= $values['pageno']['package'] == '30' ? 'selected' : '' ?>>30</option>
                                            <option value="50" <?= $values['pageno']['package'] == '50' ? 'selected' : '' ?>>50</option>
                                            <option value="100" <?= $values['pageno']['package'] == '100' ? 'selected' : '' ?>>100</option>
                                    </select>
                                </div>
                            </div>
                         
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹入库功能设置</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    唛头设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_usermark]" value="1"
                                               data-am-ucheck  <?= $values['is_usermark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_usermark]" value="0"
                                               data-am-ucheck <?= $values['is_usermark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_usermark]" value="1" data-am-ucheck
                                            <?= $values['is_force_usermark']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    国家设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_country]" value="1"
                                               data-am-ucheck  <?= $values['is_country'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_country]" value="0"
                                               data-am-ucheck <?= $values['is_country'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_country]" value="1" data-am-ucheck
                                            <?= $values['is_force_country']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    仓库设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shop]" value="1"
                                               data-am-ucheck  <?= $values['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shop]" value="0"
                                               data-am-ucheck <?= $values['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_shop]" value="1" data-am-ucheck
                                            <?= $values['is_force_shop']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    物流设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_express]" value="1"
                                               data-am-ucheck  <?= $values['is_express'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_express]" value="0"
                                               data-am-ucheck <?= $values['is_express'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_express]" value="1" data-am-ucheck
                                            <?= $values['is_force_express']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    包裹信息设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packinfo]" value="1"
                                               data-am-ucheck  <?= $values['is_packinfo'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packinfo]" value="0"
                                               data-am-ucheck <?= $values['is_packinfo'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_packinfo]" value="1" data-am-ucheck
                                            <?= $values['is_force_packinfo']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    总价值设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_totalvalue]" value="1"
                                               data-am-ucheck  <?= $values['is_totalvalue'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_totalvalue]" value="0"
                                               data-am-ucheck <?= $values['is_totalvalue'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_totalvalue]" value="1" data-am-ucheck
                                            <?= $values['is_force_totalvalue']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    物品品类设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_category]" value="1"
                                               data-am-ucheck  <?= $values['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_category]" value="0"
                                               data-am-ucheck <?= $values['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_category]" value="1" data-am-ucheck
                                            <?= $values['is_force_category']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                   备注设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_adminremark]" value="1"
                                               data-am-ucheck  <?= $values['is_adminremark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_adminremark]" value="0"
                                               data-am-ucheck <?= $values['is_adminremark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_adminremark]" value="1" data-am-ucheck
                                            <?= $values['is_force_adminremark']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                   包裹图片设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packimage]" value="1"
                                               data-am-ucheck  <?= $values['is_packimage'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packimage]" value="0"
                                               data-am-ucheck <?= $values['is_packimage'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_packimage]" value="1" data-am-ucheck
                                            <?= $values['is_force_packimage']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                   包裹存放位置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shelf]" value="1"
                                               data-am-ucheck  <?= $values['is_shelf'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shelf]" value="0"
                                               data-am-ucheck <?= $values['is_shelf'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_shelf]" value="1" data-am-ucheck
                                            <?= $values['is_force_shelf']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    集运路线设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_line]" value="1"
                                               data-am-ucheck  <?= $values['is_line'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_line]" value="0"
                                               data-am-ucheck <?= $values['is_line'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_line]" value="1" data-am-ucheck
                                            <?= $values['is_force_line']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">集运订单功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    发货订单号生成规则
                                </label>
                                <div class="am-u-sm-9">
                                    <select id="selectize-tags-1" onclick="changeorder()" onchange="changeorder()" name="adminstyle[orderno][default]" multiple="" class="tag-gradient-success">
                                        <?php if (isset($values['orderno']['model']) && isset($values['orderno']['default'])): foreach ($values['orderno']['default'] as $key =>$item): ?>
                                            <option value="<?= $item ?>" selected ><?= $values['orderno']['model'][$item] ?></option>
                                        <?php endforeach; endif; ?>
                                        
                                        <?php if (isset($values['orderno']['model']) && isset($values['orderno']['default'])): foreach ($values['orderno']['model'] as $key =>$items): ?>
                                            <option value="<?= $key ?>" <?= in_array($key,$values['orderno']['default'])?"selected":'' ?>><?= $items ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <input id="orderno" autocomplete="off" type="hidden" name="adminstyle[orderno][default]"  value="<?= implode(',',$values['orderno']['default']) ?>">
                                    <small>注：请至少选择两个规则，注意选择固定+动态的单号规则；</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 自定义发货单号首字母 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="adminstyle[orderno][first_title]"
                                           value="<?= $values['orderno']['first_title']??'' ?>" required>
                                            <div class="help-block">
                                        <small>注：当上面发货订单号生成规则选择了首字母才会使用该首字母；</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    集运订单账号费用显示设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[freestyle]" value="10"
                                               data-am-ucheck  <?= $values['freestyle'] == 10 ? 'checked' : '' ?>>
                                        显示总金额
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[freestyle]" value="20"
                                               data-am-ucheck <?= $values['freestyle'] == 20 ? 'checked' : '' ?>>
                                        显示费用明细
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    用户地址手机号是否加密
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_address_secret]" value="1"
                                               data-am-ucheck  <?= $values['is_address_secret'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_address_secret]" value="0"
                                               data-am-ucheck <?= $values['is_address_secret'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block">
                                        <small>注意：开启加密后的手机号中间四位数将会被*代替，如180****8550</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">排序功能设置</div>
                            </div>
                            <div class="am-form-group usercoded"> 
                                <label class="am-u-sm-3  am-form-label form-require"> 包裹列表排序参数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="adminstyle[packageorderby][order_mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="updated_time" <?= $values['packageorderby']['order_mode'] == 'updated_time' ? 'selected' : '' ?>>包裹更新时间</option>
                                            <option value="created_time" <?= $values['packageorderby']['order_mode'] == 'created_time' ? 'selected' : '' ?>>包裹创建时间</option>
                                            <option value="entering_warehouse_time" <?= $values['packageorderby']['order_mode'] == 'entering_warehouse_time' ? 'selected' : '' ?>>包裹入库时间</option>
                                            <option value="scan_time" <?= $values['packageorderby']['order_mode'] == 'scan_time' ? 'selected' : '' ?>>包裹查验时间</option>
                                    </select>
                                    <div class="help-block">
                                        <small>选择此参数后，包裹将按照此参与的大小进行排序，默认按照更新时间排序</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group usercoded">
                                <label class="am-u-sm-3  am-form-label form-require"> 包裹列表排序方式 </label> 
                            <div class="am-u-sm-9 am-u-end">
                                    <select name="adminstyle[packageorderby][order_type]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="desc" <?= $values['packageorderby']['order_type'] == 'desc' ? 'selected' : '' ?>>DESC(按大到小，新到旧的方式排序)</option>
                                            <option value="asc" <?= $values['packageorderby']['order_type'] == 'asc' ? 'selected' : '' ?>>DESC(按小到大，旧到新的方式排序)</option>
                                    </select>
                                    <div class="help-block">
                                        <small>选择此参数后，包裹将按照此参与的大小进行排序，默认按照更新时间排序</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group usercoded"> 
                                <label class="am-u-sm-3  am-form-label form-require"> 集运订单排序参数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="adminstyle[inpackorderby][order_mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="created_time" <?= $values['inpackorderby']['order_mode'] == 'created_time' ? 'selected' : '' ?>>提交打包时间</option>
                                            <option value="pick_time" <?= $values['inpackorderby']['order_mode'] == 'pick_time' ? 'selected' : '' ?>>查验完成时间</option>
                                            <option value="pay_time" <?= $values['inpackorderby']['order_mode'] == 'pay_time' ? 'selected' : '' ?>>支付完成时间</option>
                                            <option value="sendout_time" <?= $values['inpackorderby']['order_mode'] == 'sendout_time' ? 'selected' : '' ?>>订单发货时间</option>
                                    </select>
                                    <div class="help-block">
                                        <small>选择此参数后，包裹将按照此参与的大小进行排序，默认按照更新时间排序</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group usercoded">
                                <label class="am-u-sm-3  am-form-label form-require"> 集运订单排序方式 </label> 
                            <div class="am-u-sm-9 am-u-end">
                                    <select name="adminstyle[inpackorderby][order_type]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="desc" <?= $values['inpackorderby']['order_type'] == 'desc' ? 'selected' : '' ?>>DESC(按大到小，新到旧的方式排序)</option>
                                            <option value="asc" <?= $values['inpackorderby']['order_type'] == 'asc' ? 'selected' : '' ?>>DESC(按小到大，旧到新的方式排序)</option>
                                    </select>
                                    <div class="help-block">
                                        <small>选择此参数后，包裹将按照此参与的大小进行排序，默认按照更新时间排序</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">快递面单模板设置</div>
                            </div>
                            <div class="am-form-group usercoded"> 
                                <label class="am-u-sm-3  am-form-label form-require"> 集运面单模板 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="adminstyle[delivertempalte][orderface]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['delivertempalte']['orderface'] == '10' ? 'selected' : '' ?>>模板A</option>
                                            <option value="20" <?= $values['delivertempalte']['orderface'] == '20' ? 'selected' : '' ?>>模板B</option>
                                            <!--<option value="30" <?= $values['delivertempalte']['orderface'] == '30' ? 'selected' : '' ?>>模板C</option>-->
                                            <!--<option value="40" <?= $values['delivertempalte']['orderface'] == '40' ? 'selected' : '' ?>>模板D</option>-->
                                    </select>
                                    <div class="help-block">
                                        <small>注意：默认开启A模式，如需其他模式亲自行设置
                                              <a href="<?= url('store/setting.help/orderface') ?>" target="_blank">点击查看效果图？</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group usercoded"> 
                                <label class="am-u-sm-3  am-form-label form-require"> 集运标签模板 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="adminstyle[delivertempalte][labelface]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['delivertempalte']['labelface'] == '10' ? 'selected' : '' ?>>模板A</option>
                                            <option value="20" <?= $values['delivertempalte']['labelface'] == '20' ? 'selected' : '' ?>>模板B</option>
                                            <!--<option value="30" <?= $values['delivertempalte']['labelface'] == '30' ? 'selected' : '' ?>>模板C</option>-->
                                            <!--<option value="40" <?= $values['delivertempalte']['labelface'] == '40' ? 'selected' : '' ?>>模板D</option>-->
                                    </select>
                                    <div class="help-block">
                                        <small>注意：默认开启A模式，如需其他模式亲自行设置
                                              <a href="<?= url('store/setting.help/labelface') ?>" target="_blank">点击查看效果图？</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    用户手机号是否加密
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_phone_secret]" value="1"
                                               data-am-ucheck  <?= $values['is_phone_secret'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_phone_secret]" value="0"
                                               data-am-ucheck <?= $values['is_phone_secret'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block">
                                        <small>注意：开启加密后的手机号中间四位数将会被*代替，如180****8550</small>
                                    </div>
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
    });
</script>
