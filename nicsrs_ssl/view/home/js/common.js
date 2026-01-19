

function getwarning(data,lang) {
    $('.general-popup').remove();
    $('.sy-mask').remove();
    if($.isArray(data) ){
        var body = '';
        var headStr = '<div class="warning-box">\n' +
            '        <p>'+lang.error_message+'</p>\n' +
            '        <ul class="warning-list">\n' ;
        $.each(data, function(a, b){
            body += '<li>'+ b +'</li>\n';
        });

        var footStr = '        </ul>\n' +
            '    </div>';

        if ($('.warning-box')[0]) {
            $('.warning-box .warning-list').html(body);
        } else {
            $('#sslContent').prepend(headStr + body + footStr);
        }

        $(document).find('.warning-box').slideDown();
        $(document).scrollTop(0);
    } else if(data === 'close'){
        $(document).find('.warning-box').slideUp().find('.warning-list').html('');
    }
}

function closeAlert(obj){
    $('.general-popup').remove();
    $('.sy-mask').remove();
    syalert.syhide(obj);
}

function getAlert(id, title,lang) {
    $('.general-popup').remove();
    $('.sy-mask').remove();
    var html = '<div class="sy-alert animated general-popup" sy-enter="zoomIn" sy-leave="zoomOut" sy-type="confirm" sy-mask="true" id= '+ id +'>\n' +
        '        <div class="sy-title">'+lang.tips+'</div>\n' +
        '        <div class="sy-content">'+ title +'</div>\n' +
        '        <div class="sy-btn">\n' +
        '            <button onClick="syalert.syhide(\''+ id +'\')">'+lang.cancel+'</button>\n' +
        '            <button class="confirm">'+lang.confirm+'</button>\n' +
        '        </div>\n' +
        '    </div>';

    $('body').append(html);
    syalert.syopen(id);
}

function getTextAlert(id, title, value) {
    $('.general-popup').remove();
    $('.sy-mask').remove();
    var html = '<div class="sy-alert animated general-popup" sy-enter="zoomIn" sy-leave="zoomOut" sy-type="confirm" sy-mask="true" id= '+ id +'>\n' +
        '        <div class="sy-title">'+ title +'</div>\n' +
        '        <div class="sy-content"><span class="text-box"><input type="text" placeholder="" value="'+ value +'"></span></div>\n' +
        '        <div class="sy-btn">\n' +
        '            <button onClick="syalert.syhide(\''+ id +'\')">取消</button>\n' +
        '            <button class="confirm">确认</button>\n' +
        '        </div>\n' +
        '    </div>';

    $('#main #content').append(html);
    syalert.syopen(id);
}


function getLoadBox(status) {
    $('.general-popup').remove();
    $('.sy-mask').remove();
    if (status != 'close') {
        var html = '<div class="sy-alert animated general-popup" sy-enter="zoomIn" sy-leave="zoomOut" sy-type="confirm" sy-mask="true" id="loading">\n' +
            '        <div class="loading"><span>Loading...</span></div>\n' +
            '       </div>';
        $('body').append(html);
        syalert.syopen('loading');
    }
}

function getPromptAlert(title, status) {
    $('.general-popup').remove();
    $('.sy-mask').remove();
    if ($('#prompt-box').length == 0) {
        var html = '<div class="sy-alert sy-alert-model  general-popup animated prompt-box prompt-box-'+ status +'" sy-enter="zoomIn" sy-leave="zoomOut" sy-type="confirm" sy-mask="false" id="prompt-box">\n' +
            '        '+ title +'\n' +
            '    </div>';
        $('body').append(html);
    }
    syalert.syopen("prompt-box");

    setTimeout(function(){
        closeAlert("prompt-box")
    },2000);
}

//产品详情状态
function SetSslDetailsStatus(status) {
    var arr = {
        'subCode' : 'Order ID',
        'caCode' : 'CA Order ID',
        'billcycles' : 'Validity Period',
        'price' : 'Price',
        'created' : 'Purchase Date',
        'duedate' : 'End Date',
        'statusName' : 'Status'
    };
    var html = '';
    $.each(status, function(key, val){
        var color = '';
        if (key == 'billcycles' || key == 'statusName' && val != '已签发' ) {
            color = 'font-red';
        } else if (key == 'price' || val == '已签发') {
            color = 'font-green';
        }
        if (key != 'downPath') {
            html += '<li><div class="box">\n' +
                '     <p>' + arr[key] + '</p>\n' +
                '     <span class="'+ color +'">' + val + '</span>\n' +
                '    </div>' +
                '</li>';
        }

    });
    $('.portal-status-list').html('');
    $('.portal-status-list').html(html);
}

//ssl详情操作part
function getSslDetailsOperation(data, path) {
    path = path == '' ? '' : path;
    var arr = {
        'downCertButton': '<li class="csr-file-part "><input type="text" class="private-key-password" placeholder="password"><input type="file" id="key-file"><button id="upload-csr-file" class="b-general">Upload</button><button id="down-cert" class="b-switch" path="'+ path +'">Download</button></li>',
        'replaceButton' : '<li><button id="replace-domain">Replace</button></li>',
        'cancelButton': '<li><button class="b-cancel cancel">Cancel</button></li>',
        'downKeyButton': '<li><button id="down-private-key" class="b-switch">Download SK</button></li>',
        'verifyButton': '<li><button id="verify-email">Verify Email</button></li>'
    };
    console.log(data)
    $.each(data, function(key, value){
        if (value == 1) {
            $('.portal-details-operation').append(arr[key]);
        } else {
            // if (key == 'downCertButton') {
            //     $('.portal-domain-part .opeartion').html('<button id="domain-save" class="domain-save b-general">保存更改</button>');
            //     $('.portal-domain-part .verinfo-opeartion').append('<span class="operation" id="verification-operation">一键设置</span>');
            //     $('.portal-domain-part .email-operation').append('<span class="operation" id="email-operation">一键设置</span>');
            // }
        }
    });
}

function downloadFile(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}
function loadEffect(obj, type) {
    if (type) {
        obj.addClass('load-effect');
    } else {
        obj.removeClass('load-effect');
    }
    obj.attr('disabled', type);
}
