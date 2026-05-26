if(typeof evoRenderTvImageCheck != "function") {
    function evoRenderTvImageCheck(a) {
        var b = document.getElementById('image_for_' + a.target.id),
            c = new Image;
        a.target.value ? (c.src = '/' + a.target.value, c.onerror = function () {
            console.log('error');
            b.style.backgroundImage = '', b.setAttribute('data-image', '');
        }, c.onload = function () {
            console.log('onload');
            b.style.backgroundImage = 'url(\'' + this.src + '\')', b.setAttribute('data-image', this.src);
        }) : (b.style.backgroundImage = '', b.setAttribute('data-image', ''));
    }
}


jQuery(document).ready(function ($) {

    var directoryEditor = {
        _token: '',
        setToken: function () {
            if ($(document).find("meta[name='csrf']").length > 0) {
                this._token = $(document).find("meta[name='csrf']").attr("content");
            }
        },
        bindEditorForm: function () {
            let self = this;
            $(document).on("click", ".directory-list .directory_editor_wrapper form button[data-submit]", function (e) {
                e.preventDefault();
                let form = $(this).closest("form");
                let data = new FormData(form[0]);
                self.sendRequest('save-value', form, data, {callback: 'saveValue'});
            })
        },
        bindCeilAction: function () {
            let self = this;
            $(document).on("click", ".directory-list .editable", function (e) {

                if ($(e.target).closest('.directory_editor_wrapper').length) return;

                if ($(this).hasClass("in_proccess")) {
                    $(this).find(".directory_editor_wrapper").hide(150, function () {
                        $(this).remove();
                    });
                    $(this).removeClass("in_proccess");
                    return;
                } else {
                    //удаляем все остальные эдиторы, если они есть
                    $(document).find(".directory-list .directory_editor_wrapper").hide(150, function () {
                        $(this).remove();
                    });
                }

                let ceil = $(this);
                //ceil.addClass("in_proccess");
                let field = ceil.attr("class").split(/\s+/).find(cls => cls.includes('-column')).split('-column')[0];
                field = $.trim(field);
                let id = ceil.closest("tr").find(".id-column").text();
                id = $.trim(id);
                let data = new FormData();
                data.append('field', field);
                data.append('id', id);
                self.sendRequest('get-editor', ceil, data, {callback: 'getEditor'});
            })
        },
        getEditorSuccess: function (element, data, msg) {
            let html = msg.html || '';
            if (html.length > 0) {
                element.append(html);
                element.addClass("in_proccess");
            }
        },
        saveValueSuccess: function (element, data, msg) {
            //console.log(msg);
            let value = msg.data.renderedValue || msg.data.value;
            if(typeof value != "undefined") {
                let ceil = element.closest(".editable.in_proccess");
                ceil.html(value);
                ceil.removeClass("in_proccess");
                //ceil.append(msg.editor);
            }
        },
        sendRequest: function (url, element, data, add = {}) {
            let self = this;
            let callback = add.callback || element.data('callback') || '';
            let contentType = false;
            if (typeof data == 'string') {
                contentType = 'application/x-www-form-urlencoded;charset=UTF-8';
                data += '&_token=' + self._token;
            } else {
                data.append('_token', self._token)
            }
            url = '/api/directory-editor/' + url;
            $.ajax({
                url: url,
                data: data,
                type: add.method || "post",
                cache: false,
                processData: false,
                contentType: contentType,
                dataType: 'json',
                beforeSend: function () {
                    element.css({'opacity': .4});
                    if (callback.length > 0 && typeof self[callback + 'BeforeSend'] == "function") {
                        self[callback + 'BeforeSend'](element, data);
                    }
                },
                success: function (msg) {
                    //console.log(msg);
                    element.animate({'opacity': 1}, 300);
                    let error = msg.error || false;
                    let alert = msg.alert || '';
                    if (!error) {
                        if (callback.length > 0 && typeof self[callback + 'Success'] == "function") {
                            self[callback + 'Success'](element, data, msg);
                        }
                        if (alert.length > 0) {
                            self.alert(alert);
                        }
                    } else {
                        if (callback.length > 0 && typeof self[callback + 'Error'] == "function") {
                            self[callback + 'Error'](element, data, msg);
                        }
                        if (alert.length > 0) {
                            self.alert(alert, 'error');
                        }
                    }
                },
                error: function (msg) {
                    element.animate({'opacity': 1}, 300);
                    let alert = msg.alert || '';
                    if (callback.length > 0 && typeof self[callback + 'Error'] == "function") {
                        self[callback + 'Error'](element, data, msg);
                    }
                    if (alert.length > 0) {
                        self.alert(alert, 'error');
                    }
                },
            }).always(function (msg) {
                self.removeLoaders();
                if (typeof element.prop('disabled') != "undefined") {
                    element.prop('disabled', '');
                    element.find("[type='submit']").prop("disabled", "");
                }
                let btn = element.find("[type='submit']");
                if (btn.length > 0) {
                    btn.prop("disabled", "");
                }
            });
        },
        removeLoaders: function () {

        },
        bindCloseEditorForm: function(){
            $(document).on("click", ".directory_editor_buttons [data-cancel]", function(){
                let wrapper = $(this).closest(".directory_editor_wrapper");
                wrapper.closest(".editable.in_proccess").removeClass("in_proccess");
                wrapper.animate({'opacity': 0}, 150, function(){
                    this.remove();
                });
            })
        },
        init: function () {

            if ($(document).find(".directory-list").length < 0) return;

            this.setToken();
            this.bindCeilAction();
            this.bindEditorForm();
            this.bindCloseEditorForm();
        }
    };


    directoryEditor.init();

})