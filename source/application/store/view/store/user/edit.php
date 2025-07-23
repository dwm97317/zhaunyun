<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑管理员</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_name]"
                                           value="<?= $model['user_name'] ?>" placeholder="请输入用户名" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">所属角色 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[role_id][]" multiple data-am-selected="{btnSize: 'sm'}">
                                        <?php if (isset($roleList)): foreach ($roleList as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"
                                                <?= in_array($role['role_id'], $model['roleIds']) ? 'selected' : '' ?>>
                                                <?= $role['role_name_h1'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>注：支持多选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属仓库 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[shop_id][]" multiple
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'所有仓库', maxHeight: 400}">
                                            <option value="0"></option>
                                        <?php if (isset($shopList)): foreach ($shopList as $role): ?>
                                            <option value="<?= $role['shop_id'] ?>"
                                                <?= in_array($role['shop_id'], explode(',',$model['shop_id'])) ? 'selected' : '' ?>>
                                                <?= $role['shop_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>可以设置多个仓库，让管理员能够查看多个仓库的包裹和订单信息</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 绑定员工 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[clerk_id][]" multiple
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'所有员工', maxHeight: 400}">
                                            <option value="0"></option>
                                        <?php if (isset($clerklist)): foreach ($clerklist as $role): ?>
                                            <option value="<?= $role['clerk_id'] ?>"
                                                <?= in_array($role['clerk_id'], explode(',',$model['clerk_id'])) ? 'selected' : '' ?>>
                                                <?= $role['real_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>可以设置该账号可以查看那些员工绑定到用户，用户包裹，用户订单，不选则为可以查看所有用户所有包裹、订单等；</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 管理的路线 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[line_id][]" multiple
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'所有路线', maxHeight: 400}">
                                            <option value="0"></option>
                                        <?php if (isset($linelist)): foreach ($linelist as $item): ?>
                                            <option value="<?= $item['id'] ?>"
                                                <?= in_array($item['id'], explode(',',$model['line_id'])) ? 'selected' : '' ?>>
                                                <?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>默认情况下管理员能看到所有的路线，如果需要限制某个管理员只查看几条路线的订单，则可以设置此参数。</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 管理的国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[country_id][]" multiple
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'所有国家', maxHeight: 400}">
                                            <option value="0"></option>
                                        <?php if (isset($countrylist)): foreach ($countrylist as $item): ?>
                                            <option value="<?= $item['id'] ?>"
                                                <?= in_array($item['id'], explode(',',$model['country_id'])) ? 'selected' : '' ?>>
                                                <?= $item['title'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>默认情况下管理员能看到所有的国家的包裹或订单，如果需要限制某个管理员只查看几个国家的包裹和订单，则可以设置此参数。</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">登录密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password]"
                                           value="" placeholder="请输入登录密码">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">确认密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password_confirm]"
                                           value="" placeholder="请输入确认密码">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">姓名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[real_name]"
                                           value="<?= $model['real_name'] ?>">
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
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
