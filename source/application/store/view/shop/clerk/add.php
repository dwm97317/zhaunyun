<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加员工</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label form-require"> 选择用户 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属仓库 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="clerk[shop_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}"
                                            required>
                                        <option value=""></option>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择店员所属的仓库</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 员工姓名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="clerk[real_name]"
                                           placeholder="请输入店员姓名" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 手机号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="clerk[mobile]"
                                           placeholder="请输入手机号" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="clerk[password]"
                                           placeholder="请输入密码" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 发货仓入库员 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_datatop]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_datatop']) && $model['clerk_authority']['is_datatop'] == 1 ? 'checked' : '' ?>>
                                        PDA快速入库
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_fahuoin]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_fahuoin']) && $model['clerk_authority']['is_fahuoin'] == 1 ? 'checked' : '' ?>>
                                        发货仓扫码入库
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_fahuolist]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_fahuolist']) && $model['clerk_authority']['is_fahuolist'] == 1 ? 'checked' : '' ?>>
                                        发货仓包裹列表
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_zhiyoufahuoin]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_zhiyoufahuoin']) && $model['clerk_authority']['is_zhiyoufahuoin'] == 1 ? 'checked' : '' ?>>
                                        直邮包裹入库
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_userpacklist]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_userpacklist']) && $model['clerk_authority']['is_userpacklist'] == 1 ? 'checked' : '' ?>>
                                        用户入库包裹
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_calimpackage]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_calimpackage']) && $model['clerk_authority']['is_calimpackage'] == 1 ? 'checked' : '' ?>>
                                        客户认领包裹
                                    </label>
                                    <div class="help-block">
                                        <small>入库员: 包裹入库时,进行入库操作;</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 到达仓入库员 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_daodain]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_daodain']) && $model['clerk_authority']['is_daodain'] == 1 ? 'checked' : '' ?>>
                                        扫码入库
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_daodalist]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_daodalist']) && $model['clerk_authority']['is_daodalist'] == 1 ? 'checked' : '' ?>>
                                        包裹列表
                                    </label>
                                    <div class="help-block">
                                        <small>入库员: 包裹入库时,进行入库操作;</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 分拣员 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_saomashangjia]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_saomashangjia']) && $model['clerk_authority']['is_saomashangjia'] == 1 ? 'checked' : '' ?>>
                                        扫码上架
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_saomaxiajia]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_saomaxiajia']) && $model['clerk_authority']['is_saomaxiajia'] == 1 ? 'checked' : '' ?>>
                                        扫码下架
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_packageture]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_packageture']) && $model['clerk_authority']['is_packageture'] == 1 ? 'checked' : '' ?>>
                                        包裹转移
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_problem]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_problem']) && $model['clerk_authority']['is_problem'] == 1 ? 'checked' : '' ?>>
                                        问题件
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_scanquery]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_scanquery']) && $model['clerk_authority']['is_scanquery'] == 1 ? 'checked' : '' ?>>
                                        扫码查件
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_shelfmanagement]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_shelfmanagement']) && $model['clerk_authority']['is_shelfmanagement'] == 1 ? 'checked' : '' ?>>
                                        货位管理
                                    </label>
                                    <div class="help-block">
                                        <small>分拣员: 进行包裹上下架操作;</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 打包员 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_fengxiang]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_fengxiang']) && $model['clerk_authority']['is_fengxiang'] == 1 ? 'checked' : '' ?>>
                                        订单封箱
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_dabaolist]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_dabaolist']) && $model['clerk_authority']['is_dabaolist'] == 1 ? 'checked' : '' ?>>
                                        打包列表
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_saomadabao]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_saomadabao']) && $model['clerk_authority']['is_saomadabao'] == 1 ? 'checked' : '' ?>>
                                        扫码打包
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_saomachuku]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_saomachuku']) && $model['clerk_authority']['is_saomachuku'] == 1 ? 'checked' : '' ?>>
                                        扫码出库
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_fenjiandan]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_fenjiandan']) && $model['clerk_authority']['is_fenjiandan'] == 1 ? 'checked' : '' ?>>
                                        扫分拣单
                                    </label>
                                    <div class="help-block">
                                        <small>打包员负责包裹的快速扫码打包，出库，封箱操作</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 签收员 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_chajianqianshou]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_chajianqianshou']) && $model['clerk_authority']['is_chajianqianshou'] == 1 ? 'checked' : '' ?>>
                                        查件签收
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_daipaijian]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_daipaijian']) && $model['clerk_authority']['is_daipaijian'] == 1 ? 'checked' : '' ?>>
                                        待派件  
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_shangmenqujian]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_shangmenqujian']) && $model['clerk_authority']['is_shangmenqujian'] == 1 ? 'checked' : '' ?>>
                                        上门取件
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_kuaisuludan]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_kuaisuludan']) && $model['clerk_authority']['is_kuaisuludan'] == 1 ? 'checked' : '' ?>>
                                        快速录单
                                    </label>
                                    <div class="help-block">
                                        <small>自提点或者海外仓进行包裹的管理工作;</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 仓管员(仓库总管) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_datacenter]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_datacenter']) && $model['clerk_authority']['is_datacenter'] == 1 ? 'checked' : '' ?>>
                                        数据中心
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_shopadmin]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_shopadmin']) && $model['clerk_authority']['is_shopadmin'] == 1 ? 'checked' : '' ?>>
                                        仓管中心
                                    </label>
                                    <div class="help-block">
                                        <small>仓库总管能够查看整个包裹的出入库数据;</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 客服人员 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_myuser]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_myuser']) && $model['clerk_authority']['is_myuser'] == 1 ? 'checked' : '' ?>>
                                        我的客户
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="clerk[clerk_authority][is_myuserpackage]" value="1" data-am-ucheck
                                        <?= isset($model['clerk_authority']['is_myuserpackage']) && $model['clerk_authority']['is_myuserpackage'] == 1 ? 'checked' : '' ?>>
                                        客户包裹
                                    </label>
                                    <div class="help-block">
                                        <small>客服人员可以查看自己发展的客户的包裹情况和查看客户基本信息;</small>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="am-form-group">-->
                            <!--    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 监管仓 </label>-->
                            <!--    <div class="am-u-sm-9 am-u-end">-->
                            <!--        <label class="am-checkbox-inline">-->
                            <!--            <input type="checkbox" name="clerk[clerk_authority][is_rfidjianguanshoujian]" value="1" data-am-ucheck-->
                            <!--            <?= isset($model['clerk_authority']['is_rfidjianguanshoujian']) && $model['clerk_authority']['is_rfidjianguanshoujian'] == 1 ? 'checked' : '' ?>>-->
                            <!--            RFID收件-->
                            <!--        </label>-->
                            <!--        <label class="am-checkbox-inline">-->
                            <!--            <input type="checkbox" name="clerk[clerk_authority][is_rfidjianguanchuku]" value="1" data-am-ucheck-->
                            <!--            <?= isset($model['clerk_authority']['is_rfidjianguanchuku']) && $model['clerk_authority']['is_rfidjianguanchuku'] == 1 ? 'checked' : '' ?>>-->
                            <!--            RFID出库-->
                            <!--        </label>-->
                            <!--        <div class="help-block">-->
                            <!--            <small>清关的监管仓进行包裹的RFID批量出入库工作;</small>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="am-form-group">-->
                            <!--    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 配送员 </label>-->
                            <!--    <div class="am-u-sm-9 am-u-end">-->
                            <!--        <label class="am-checkbox-inline">-->
                            <!--            <input type="checkbox" name="clerk[clerk_authority][is_rfidpeisongshoujian]" value="1" data-am-ucheck-->
                            <!--            <?= isset($model['clerk_authority']['is_rfidpeisongshoujian']) && $model['clerk_authority']['is_rfidpeisongshoujian'] == 1 ? 'checked' : '' ?>>-->
                            <!--            RFID收件-->
                            <!--        </label>-->
                            <!--        <label class="am-checkbox-inline">-->
                            <!--            <input type="checkbox" name="clerk[clerk_authority][is_rfidpeisongqianshou]" value="1" data-am-ucheck-->
                            <!--            <?= isset($model['clerk_authority']['is_rfidpeisongqianshou']) && $model['clerk_authority']['is_rfidpeisongqianshou'] == 1 ? 'checked' : '' ?>>-->
                            <!--            RFID签收-->
                            <!--        </label>-->
                            <!--        <div class="help-block">-->
                            <!--            <small>配送员通过RFID批量收件后，进行一对一配送，配送到的包裹通过RFID识别签收;</small>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="am-form-group">-->
                            <!--    <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 海外仓 </label>-->
                            <!--    <div class="am-u-sm-9 am-u-end">-->
                            <!--        <label class="am-checkbox-inline">-->
                            <!--            <input type="checkbox" name="clerk[clerk_authority][is_haiwaicangin]" value="1" data-am-ucheck-->
                            <!--            <?= isset($model['clerk_authority']['is_haiwaicangin']) && $model['clerk_authority']['is_haiwaicangin'] == 1 ? 'checked' : '' ?>>-->
                            <!--            RFID入库-->
                            <!--        </label>-->
                            <!--        <label class="am-checkbox-inline">-->
                            <!--            <input type="checkbox" name="clerk[clerk_authority][is_haiwaicangout]" value="1" data-am-ucheck-->
                            <!--            <?= isset($model['clerk_authority']['is_haiwaicangout']) && $model['clerk_authority']['is_haiwaicangout'] == 1 ? 'checked' : '' ?>>-->
                            <!--            RFID出库-->
                            <!--        </label>-->
                                
                            <!--        <div class="help-block">-->
                            <!--            <small>在包裹到达海外仓后，海外仓通过RFID批量进行包裹入库</small>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 开启打包消息 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[mes_status]" value="0" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[mes_status]" value="1" data-am-ucheck>
                                        禁用
                                    </label>
                                    <div class="help-block"><small>开启后可以接收到用户提交打包的消息通知</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 接收打包完成通知 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[send_status]" value="0" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[send_status]" value="1" data-am-ucheck>
                                        禁用
                                    </label>
                                    <div class="help-block"><small>开启后可以接收到打包完成的消息通知</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 预约上门通知 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[visit_status]" value="0" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[visit_status]" value="1" data-am-ucheck>
                                        禁用
                                    </label>
                                    <div class="help-block"><small>开启后可以接收到打包完成的消息通知</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 认领通知 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[claim_status]" value="0" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[claim_status]" value="1" data-am-ucheck>
                                        禁用
                                    </label>
                                    <div class="help-block"><small>开启后可以接收到用户认领包裹的消息通知</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[status]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="clerk[status]" value="0" data-am-ucheck>
                                        禁用
                                    </label>
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
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="clerk[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>

<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 选择用户
        $('.j-selectUser').click(function () {
            var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    var user = [data[0]];
                    $userList.html(template('tpl-user-item', user));
                }
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
