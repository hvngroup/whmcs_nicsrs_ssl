
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/syalert.min.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/select.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/common.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/animate.min.css" media="screen" rel="stylesheet" type="text/css">
    <style>
        #sslContent{
            background-color: #f8f8f8;
            min-height: 800px;
        }
        .main-content{
            background-color: #f8f8f8;
        }
        #csrPart{
            width: 100%;
            height: auto;
            background-color: #ffffff;
            box-sizing: border-box;
            margin-top: 20px;
        }
        #orderInfoPart{
            width: 100%;
            height: auto;
            background-color: #ffffff;
            box-sizing: border-box;
            margin-top: 20px;
        }
        .orderInfoInput{
            font-size: 14px;
            color: #000000;
            margin-top: 20px;
        }
        .orderInfoInput .orderInfoDetail{
            display: inline-block;
            height: 35px;
            line-height: 35px;
            width: 40%;
        }
        .orderInfoInput .orderDetailContent{
            display: inline-block;
            margin-left: 20px;
        }
        .orderInfoInput .applyStatus{
            color: red;
        }
        #domainPart{
            width: 100%;
            height: auto;
            background-color: #ffffff;
            box-sizing: border-box;
            margin-top: 20px;
        }
        #submitPart{
            width: 100%;
            height: 100px;
            line-height: 100px;
            text-align: left;
        }
        #submitPart .submit-button{
            width: 100px;
            height: 40px;
            line-height: 40px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            background-color: #0e5077;
        }
        #personalcontactPart,#organizationPart{
            width: 100%;
            height: auto;
            background-color: #ffffff;
            box-sizing: border-box;
            margin-top: 20px;
        }
        .partpadding{
            padding: 20px 25px;
        }
        #renewornotPart .leftTitle{
            width: 80%;
            height: 100%;
        }
        #renewornotPart .rightselect{
            width: 20%;
            height: 100%;
        }
        .title span{
            width: 22px;
            height: 22px;
            background-color: #0e5077;
            font-size: 18px;
            color: #ffffff;
            display: inline-block;
            text-align: center;
            line-height: 22px;
        }
        .title .operatorButton{
            float: right;
            display: flex;
        }
        .title .operatorButton button{
            min-width: 70px;
            font-size: 14px;
            height: 35px;
            line-height: 35px;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
            color: white;
        }
        .title .operatorButton .downcert{
            background-color: #0e5077;
        }
        .title .operatorButton .downkey{
            background-color: #0e5077;
        }
        .title .operatorButton .replace{
            background-color: #0a8fbd;
        }
        .title .operatorButton .cancleorder{
            background-color: #e95513;
        }
        .title p{
            display: inline-block;
            margin: 0px 0px 0px 15px;
            font-size: 18px;

        }
        .titleDescribe{
            margin-top: 20px;
        }
        .titleDescribe span{
            display: inline-block;
            width: 50%;
            font-size: 14px;
            color: #666666;
        }
        .topTitleDescribe{
            margin-top: 20px;
        }
        .topTitleDescribe span{
            display: inline-block;
            font-size: 14px;
            color: #666666;
            width: 90%;
        }
        .rightselect{
            text-align: right;
            line-height: 84px;
            font-size: 14px;
        }
        .rightselect .radioRight{
            margin-left: 40px;
        }
        .rightselect .renewradio{
            vertical-align:middle;
            margin-bottom: 6px;
        }
        .topTitle{
            width: 100%;
        }
        .title{
            width: 100%;
        }
        .csrInput{
            font-size: 14px;
            color: #000000;
            margin-top: 10px;
        }
        .csrInput .csrInputTitle{
            vertical-align:middle
        }
        .csrInput #isManualCsr{
            vertical-align:middle
        }
        #csr{
            width: 100%;
            margin-top: 10px;
            height: 360px;
            background-color: #f7f7f7;
            border: 1px solid black;
        }
        .domainInput{
            font-size: 14px;
            color: #000000;
            margin-top: 20px;
        }
        .personalcontactInput,.organizationInput{
            font-size: 14px;
            color: #000000;
            margin-top: 20px;
        }
        .personalcontactInput table,.organizationInput table{
            width: 100%;
        }
        .personalcontactInput td,.organizationInput td{
            width: 50%;
            padding-bottom: 20px;

        }
        .personalcontactInput input,.organizationInput input,.organizationInput select,.personalcontactInput select{
            width: 50%;
            height: 35px;
            line-height: 35px;
            border: 1px solid #e6e6e6;
            box-sizing: border-box;
            padding: 5px 10px;
        }
        .personalcontactInput .inputTitle,.organizationInput .inputTitle{
            display: inline-block;
            width: 70px;
            height: 35px;
            line-height: 35px;
            text-align: left;
        }
        .domainInput table{
            width: 100%;
        }
        .domainInput td{
            width: 33.3%;
            padding-bottom: 20px;
        }
        .domainInput thead td:first-child{
            padding-left: 20px;
        }
        .domainInput select{
            width: 200px;
            height: 100px;
            line-height: 100px;
        }
        .domainInput .domainName{
            width: 200px;
            height: 35px;
            line-height: 35px;
            border: 1px solid #e6e6e6;
        }
        .domainInput input{
            box-sizing: border-box;
            padding: 5px 10px;
        }
        .domainInput .domainNumber{
            display: inline-block;
            height: 35px;
            line-height: 35px;
            width: 20px;
        }
        .domainInput .add-line{
            font-size: 14px;
            color: green;
            cursor: pointer;
            margin-left: 20px;
        }
        .domainInput .delete-domain{
            color: white;
            cursor: pointer;
            width: 90px;
            height: 30px;
            line-height: 30px;
            background-color: #0e5077;
            border: none;
            border-radius: 5px;
        }
    </style>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/select2.js"></script>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/syalert.min.js"></script>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/FileSaver.min.js"></script>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/common.js"></script>

    <h2>{$productCode}</h2>
    {if $status == "cancelled"}
        <div class="alert alert-danger">
            {$_LANG['cancelled_des']}
        </div>
    {else}
        <div class="alert alert-info">
            {$_LANG['complete_des']}
        </div>
    {/if}
<div id="sslContent">

<div id="orderInfoPart" class="partpadding">
    <div class="topTitle">
        <div class="title">
            <span class="titlenumber">1</span><p>{$_LANG['order_info']}</p>
            {if $status == 'complete'}
            <div class="operatorButton">
                <button class="downcert">{$_LANG['down_cert']}</button>
                <button class="downkey">{$_LANG['down_key']}</button>
                <button class="replace">{$_LANG['repalce']}</button>
                <button class="cancleorder">{$_LANG['cancel']}</button>
            </div>
            {/if}
        </div>
    </div>
    <div class="orderInfoInput">
        <span class="orderInfoDetail">{$_LANG['ca_order_id']}<span class="orderDetailContent" name="vendorId"></span></span>
        <span class="orderInfoDetail">{$_LANG['cert_status']}<span class="orderDetailContent applyStatus" name="applyStatus"></span>{$status}</span>
        <span class="orderInfoDetail">{$_LANG['cert_begin']}<span class="orderDetailContent applyStatus" name="applyStatus"></span>{$begin_date}</span>
        <span class="orderInfoDetail">{$_LANG['cert_end']}<span class="orderDetailContent applyStatus" name="applyStatus"></span>{$end_date}</span>
    </div>
</div>

    {if $sslType == 'website_ssl'}
<div id="domainPart" class="partpadding">
    <div class="topTitle">
        <div class="title"><span class="titlenumber">1</span><p>{$_LANG['domain_info']}</p></div>
    </div>
    <div class="domainInput">
        <table>
            <thead>
            <tr>
                <td>{$_LANG['domain']}</td>
                <td>{$_LANG['dcv_method']}</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><span class="domainNumber">1</span><input type="text" class="domainName" name="domainName" disabled placeholder="{$_LANG['domain']}"></td>
                <td>
                    <select name="dcvMethod" disabled>
                        <option value="HTTP_CSR_HASH">{$_LANG['http_csr_hash']}</option>
                        <option value="CNAME_CSR_HASH">{$_LANG['cname_csr_hash']}</option>
                        <option value="HTTPS_CSR_HASH">{$_LANG['https_csr_hash']}</option>
                    </select>
                </td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
    {/if}
        <div id="personalcontactPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>{$_LANG['contacts']}</p></div>
            </div>
            <div class="personalcontactInput">
                <table>
                    <tbody>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['organization_name']}</span><input type="text" disabled name="adminOrganizationName"></td>
                        <td><span class="inputTitle">{$_LANG['title']}</span><input type="text" disabled name="adminTitle" ></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['first_name']}</span><input type="text" disabled name="adminFirstName"></td>
                        <td><span class="inputTitle">{$_LANG['last_name']}</span><input type="text" disabled name="adminLastName"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['email_address']}</span><input type="text" disabled name="adminEmail"></td>
                        <td><span class="inputTitle">{$_LANG['phone']}</span><input type="text" disabled name="adminPhone"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['country']}</span>
                            <input type="text" disabled name="adminCountry">
                        </td>
                        <td><span class="inputTitle">{$_LANG['address']}</span><input type="text" disabled name="adminAddress"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['city']}</span><input type="text" disabled name="adminCity"></td>
                        <td><span class="inputTitle">{$_LANG['province']}</span><input type="text" disabled name="adminProvince"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['post_code']}</span><input type="text" disabled name="adminPostCode"></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

    {if $sslValidationType != 'dv'}
    <div id="organizationPart"  class="partpadding">
        <div class="topTitle">
            <div class="title"><span class="titlenumber">6</span><p>{$_LANG['organization_info']}</p></div>
        </div>
        <div class="organizationInput">
            <table>
                <tbody>
                <tr>
                    <td><span class="inputTitle">{$_LANG['organization_name']}</span><input type="text" name="organizationName"></td>
                    <td><span class="inputTitle">{$_LANG['address']}</span><input type="text" name="organizationAddress"></td>
                </tr>
                <tr>
                    <td><span class="inputTitle">{$_LANG['country']}</span>
                        <select name="organizationCountry">
                        </select></td>
                    <td><span class="inputTitle">{$_LANG['city']}</span><input name="organizationCity" type="text"></td>
                </tr>
                <tr>
                    <td><span class="inputTitle">{$_LANG['mobile_number']}</span><input name="organizationMobile" type="text"></td>
                    <td><span class="inputTitle">{$_LANG['post_code']}</span><input name="organizationPostCode" type="text"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    {/if}
    <input type="hidden" name="other" id="other" value='{$other}'>
    <input type="hidden" name="data" id="data" value='{$data}'>
    <input type="hidden" name="lang" id="lang" value='{$_LANG_JSON}'>
</div>

{literal}
<script type="text/javascript">
    function deleteDomainTr(e){
        e.parents('tr').remove();
        initDomainNumber();
    }
    function initDomainNumber(){
        $(".domainInput tbody tr").each(function (index,value) {
            var number = index + 1;
            $(this).find(".domainNumber").text(number)
        })
    }
    function checkEmail(email){
        var email = email || '';
        var rep =/^[_a-zA-Z0-9-\u4E00-\u9FA5]+(\.[_a-zA-Z0-9-\u4E00-\u9FA5]+)*@[a-zA-Z0-9-\u4E00-\u9FA5]+(\.[a-zA-Z0-9-\u4E00-\u9FA5]+)*(\.[a-zA-Z0-9\u4E00-\u9FA5]{2,})$/u;
        if(rep.test(email)){
            return true;
        }
        return  false;
    }
    function addDomainTr(domainName,dcvMethod,lang){
        var domainName = domainName || '';
        var dcvMethod = dcvMethod || '';
        var dcvMethodStr = '<select name="dcvMethod">\n' +
                '                            <option value="">'+lang.please_choose+'</option>\n' +
                '                            <option value="HTTP_CSR_HASH">'+lang.http_csr_hash+'</option>\n' +
                '                            <option value="CNAME_CSR_HASH">'+lang.cname_csr_hash+'</option>\n' +
                '                            <option value="HTTPS_CSR_HASH">'+lang.https_csr_hash+'</option> ';
        if(checkEmail(dcvMethod)){
            dcvMethodStr += '<option value="'+dcvMethod+'">'+dcvMethod+'</option>'
        }
        dcvMethodStr += '</select>';
        var addBeforLen =  $(".domainInput tbody tr:last-child").find(".domainNumber").text();
        var addLine = '<tr>\n' +
                '                    <td><span class="domainNumber">1</span></spa><input type="text" class="domainName" name="domainName" value="'+domainName+'" placeholder="'+lang.domain+'"></td>\n' +
                '                    <td>\n' +dcvMethodStr+
                '                    </td>\n' +
                '                    <td>\n' +
                '                    </td>\n' +
                '                </tr>';
        $(".domainInput tbody").append(addLine);
        var addSelect = $("select[name='dcvMethod']:last-child").val(dcvMethod).select2();
        addBeforLen++ ;
        $(".domainInput tbody tr:last-child").find(".domainNumber").text(addBeforLen)
    }
    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return (false);
    }
    function initDatas(lang){
        try{
            var configData = $("#data").val();

            if(configData){
                configData = JSON.parse(configData);
                if(configData.csr){
                    $("#csr").val(configData.csr)
                }
                if(!configData.privateKey){
                    $(".downkey").hide()
                }

                if(configData.Administrator){
                    $("#personalcontactPart [name='adminOrganizationName']").val(configData.Administrator.organation);
                    $("#personalcontactPart [name='adminTitle']").val(configData.Administrator.job);
                    $("#personalcontactPart [name='adminFirstName']").val(configData.Administrator.firstName);
                    $("#personalcontactPart [name='adminLastName']").val(configData.Administrator.lastName);
                    $("#personalcontactPart [name='adminEmail']").val(configData.Administrator.email);
                    $("#personalcontactPart [name='adminPhone']").val(configData.Administrator.mobile);
                    $("#personalcontactPart [name='adminCountry']").val(configData.Administrator.country);
                    $("#personalcontactPart [name='adminAddress']").val(configData.Administrator.address);
                    $("#personalcontactPart [name='adminCity']").val(configData.Administrator.city);
                    $("#personalcontactPart [name='adminProvince']").val(configData.Administrator.state);
                    $("#personalcontactPart [name='adminPostCode']").val(configData.Administrator.postCode)
                }

                if(configData.organizationInfo){
                    $("#organizationPart [name='organizationName']").val(configData.organizationInfo.organizationName);
                    $("#organizationPart [name='organizationAddress']").val(configData.organizationInfo.organizationAddress);
                    $("#organizationPart [name='organizationCity']").val(configData.organizationInfo.organizationCity);
                    $("#organizationPart [name='organizationCountry']").val(configData.organizationInfo.organizationCountry);
                    $("#organizationPart [name='organizationState']").val(configData.organizationInfo.organizationState);
                    $("#organizationPart [name='organizationPostCode']").val(configData.organizationInfo.organizationPostCode);
                    $("#organizationPart [name='organizationMobile']").val(configData.organizationInfo.organizationMobile);
                }
                if(configData.applyReturn.vendorId){
                    $("[name='vendorId']").text(configData.applyReturn.vendorId)
                }
                if(configData.domainInfo ){
                    var keys = Object.keys(configData.domainInfo);
                    if(keys.length > 0){
                        var nowDomainCount = $(".domainInput tbody tr").length;
                        var domainNumber = 1;
                        var domainInfos = configData.domainInfo;
                        $.each(domainInfos,function (index,value) {
                            if(domainNumber <= nowDomainCount){
                                var trnumber = domainNumber - 1;
                                $(".domainInput tbody tr").eq(trnumber).find("[name='domainName']").val(value.domainName);
                                if($(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").find("option[value='"+value.dcvMethod+"']").length > 0){
                                    $(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").val(value.dcvMethod).select2();
                                }else{
                                    var otherOptionStr = "<option value='"+value.dcvMethod+"'>"+value.dcvMethod+"</option>";
                                    $(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").append(otherOptionStr).val(value.dcvMethod).select2();;
                                }
                                domainNumber++;
                            }else{
                                addDomainTr(value.domainName,value.dcvMethod,lang)
                                domainNumber++;
                            }
                        })
                    }
                }
            }
        }catch (e) {
            return false;
        }
    }
    function downloadFile(filename,filecontent){
        var filename = filename || '';
        var filecontent = filecontent || '';
        var p = "undefined" != typeof Uint8Array ? Uint8Array: Array,
                r = "+".charCodeAt(0),
                a = "/".charCodeAt(0),
                o = "0".charCodeAt(0),
                c = "a".charCodeAt(0),
                d = "A".charCodeAt(0),
                s = "-".charCodeAt(0),
                n = "_".charCodeAt(0);
        function h(e) {
            var t = e.charCodeAt(0);
            return t === r || t === s ? 62 : t === a || t === n ? 63 : t < o ? -1 : t < o + 10 ? t - o + 26 + 26 : t < d + 26 ? t - d: t < c + 26 ? t - c + 26 : void 0
        }
        function toByteArray(e) {
            var t, r, a, o, c, d;
            if (0 < e.length % 4) throw new Error("Invalid string. Length must be a multiple of 4");
            var s = e.length;
            c = "=" === e.charAt(s - 2) ? 2 : "=" === e.charAt(s - 1) ? 1 : 0,
                    d = new p(3 * e.length / 4 - c),
                    a = 0 < c ? e.length - 4 : e.length;
            var n = 0;
            function l(e) {
                d[n++] = e
            }
            for (r = t = 0; t < a; t += 4, r += 3) l((16711680 & (o = h(e.charAt(t)) << 18 | h(e.charAt(t + 1)) << 12 | h(e.charAt(t + 2)) << 6 | h(e.charAt(t + 3)))) >> 16),
                    l((65280 & o) >> 8),
                    l(255 & o);
            return 2 === c ? l(255 & (o = h(e.charAt(t)) << 2 | h(e.charAt(t + 1)) >> 4)) : 1 === c && (l((o = h(e.charAt(t)) << 10 | h(e.charAt(t + 1)) << 4 | h(e.charAt(t + 2)) >> 2) >> 8 & 255), l(255 & o)), d
        }

        var t = toByteArray(filecontent),
                r = new Blob([t]);
        saveAs(r, filename);

    }

    $(function (){

        var lang = $("input[name='lang']").val();
        lang = $.parseJSON(lang);
       
        if($("select[name='organizationCountry']").length > 0){
            var countries = $.parseJSON($("#other").val()).countries;
            var select2Countries = [];
            $.each(countries, function (index, obj) {
                var newcountryJson = {"id":obj.code,"text":obj.name}
                select2Countries.push(newcountryJson)
            })
            $("select[name='organizationCountry']").select2({
                'data':select2Countries,
                'placeholder': lang.please_choose
            })

        }
        if($("select[name='adminCountry']").length > 0){
            var countries = $.parseJSON($("#other").val()).countries;
            var select2Countries = [];
            $.each(countries, function (index, obj) {
                var newcountryJson = {"id":obj.code,"text":obj.name}
                select2Countries.push(newcountryJson)
            })
            $("select[name='adminCountry']").select2({
                'data':select2Countries,
                'placeholder': lang.please_choose
            })

        }

        var titlenumber = 1;
        $(".titlenumber:visible").each(function(){
            $(this).text(titlenumber);
            titlenumber ++;
        })
        $("select[name='dcvMethod']").select2();
        initDatas(lang);
        $(".replace").click(function () {
            getAlert('replaceorder',lang.sure_to_replace,lang);
            $("#replaceorder .confirm").unbind('click').click(function () {
                syalert.syhide('replaceorder');
                getLoadBox('open');
                var id = getQueryVariable("id");
                $.ajax({
                    url: "clientarea.php?action=productdetails&id=" + id + "&step=applyReplace",
                    type: "post",
                    data: {},
                    timeout: 30000,
                    success: function (data) {
                        data = $.parseJSON(data);
                        getLoadBox('close');
                        if (data.status == 0) {
                            getwarning(data.error,lang)
                            return false;
                        }
                        getPromptAlert(lang.operate_suc,'success');
                        window.location.reload();
                        return false;

                    },
                    error: function (data) {
                        getLoadBox('close');
                        var json = JSON.parse(data.responseText);
                        getPromptAlert(lang.oprate_fail,'fail');
                        return false;

                    }
                });
            })
        })
        $(".downkey").click(function(){
            getLoadBox('open')
            var id = getQueryVariable("id");
            $.ajax({
                url: "clientarea.php?action=productdetails&id=" + id + "&step=downkey",
                type: "post",
                data: {},
                timeout: 30000,
                success: function (data) {
                    data = $.parseJSON(data);
                    getLoadBox('close');
                    if (data.status == 0) {
                        getwarning(data.error,lang)
                        return false;
                    }
                    downloadFile(data.data.name,data.data.content)

                },
                error: function (data) {
                    getLoadBox('close');
                    var json = JSON.parse(data.responseText);
                    getPromptAlert(lang.oprate_fail,'fail');
                    return false;

                }
            });
        })
        $(".downcert").click(function(){
            getLoadBox('open')
            var id = getQueryVariable("id");
            $.ajax({
                url: "clientarea.php?action=productdetails&id=" + id + "&step=downcert",
                type: "post",
                data: {},
                timeout: 30000,
                success: function (data) {
                    data = $.parseJSON(data);
                    getLoadBox('close');
                    if (data.status == 0) {
                        getwarning(data.error,lang)
                        return false;
                    }
                    downloadFile(data.data.name,data.data.content)

                },
                error: function (data) {
                    getLoadBox('close');
                    var json = JSON.parse(data.responseText);
                    getPromptAlert(lang.oprate_fail,'fail');
                    return false;

                }
            });
        })

        $(".cancleorder").click(function () {
            getAlert('cancleorder',lang.sure_to_cancel,lang);
            $("#cancleorder .confirm").unbind('click').click(function () {
                syalert.syhide('cancleorder');
                getLoadBox('open');
                var id = getQueryVariable("id");
                $.ajax({
                    url: "clientarea.php?action=productdetails&id=" + id + "&step=cancleOrder",
                    type: "post",
                    data: {},
                    timeout: 30000,
                    success: function (data) {
                        data = $.parseJSON(data);
                        getLoadBox('close');
                        if (data.status == 0) {
                            getwarning(data.error,lang)
                            return false;
                        }
                        getPromptAlert(lang.operate_suc,'success');
                        window.location.reload();
                        return false;

                    },
                    error: function (data) {
                        getLoadBox('close');
                        var json = JSON.parse(data.responseText);
                        getPromptAlert(lang.oprate_fail,'fail');
                        return false;

                    }
                });
            })
        })
    })
</script>
{/literal}
