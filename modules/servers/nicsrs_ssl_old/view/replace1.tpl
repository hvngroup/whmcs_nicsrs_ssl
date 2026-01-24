
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/syalert.min.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/honeySwitch.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/select.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/common.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/animate.min.css" media="screen" rel="stylesheet" type="text/css">
    <style>

        .firstbutton {
            background-color: #229cbe;
        }

        .main-content{
            background-color: #f8f8f8;
        }
        #renewornotPart{
            width: 100%;
            height: 124px;
            background-color: #ffffff;
            display: flex;
            box-sizing: border-box;
            margin-top: 20px;
        }
        .validationInputTip{
            border-color: red !important;
        }
        #csrPart{
            width: 100%;
            height: auto;
            background-color: #ffffff;
            box-sizing: border-box;
            margin-top: 20px;
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
            height: auto;
            background-color: #ffffff;
        }
        #submitPart .submit-button{
            width: 115px;
            height: 35px;
            line-height: 35px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            background-color: #0e5077;
            margin-right: 20px;
        }
        #submitPart .draft-button{
            width: 115px;
            height: 35px;
            line-height: 35px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            background-color: #5fc3da;
            margin-right: 20px;
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
            width: 90%;
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
        .domainVrified{
            color: #0e5077;
            font-size: 14px;
            height: 30px;
            line-height: 30px;
            margin-right: 20px;
            display: none;
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
        .csrInput{
            font-size: 14px;
            color: #000000;
            margin-top: 20px;
        }
        .csrInput .csrInputTitle{
            vertical-align:middle
        }
        .csrInput #isManualCsr{
            vertical-align:middle
        }
        #csr{
            width: 100%;
            height: 360px;
            background-color: #f7f7f7;
            border: 1px solid black;
            display: none;
        }
        .csrRedBorder{
            border-color: red !important;
        }
        .csrTipError{
            color: red;
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
        .personalcontactInput input,.organizationInput input,.organizationInput select{
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
            border-radius: 5px;
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
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/honeySwitch.js"></script>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/select2.js"></script>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/syalert.min.js"></script>
    <script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/common.js"></script>

    <h2>{$productCode}</h2>
    <div class="alert alert-info">
        {$_LANG['replace_des']}
    </div>
<div id="sslContent">

    {if $sslType == 'website_ssl'}
        <div id="csrPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>CSR</p></div>
            </div>
            <div class="topTitleDescribe">
                <span>{$_LANG['csr_des_replace']}</span>
            </div>
            <div class="csrInput">
                <div class="csrTipError"></div>
                <textarea id="csr"></textarea>
            </div>
        </div>
        <div id="domainPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">3</span><p>{$_LANG['domain_info']}</p></div>
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
                        <td><span class="domainNumber">1</span><input type="text"class="domainName" placeholder="{$_LANG['domain']}" name="domainName"></td>
                        <td>
                            <select name="dcvMethod">
                                <option value="">{$_LANG['please_choose']}</option>
                                <option value="HTTP_CSR_HASH">{$_LANG['http_csr_hash']}</option>
                                <option value="CNAME_CSR_HASH">{$_LANG['cname_csr_hash']}</option>
                                <option value="HTTPS_CSR_HASH">{$_LANG['https_csr_hash']}</option>
                            </select>
                        </td>
                        <td><span class="domainVrified">{$_LANG['verified']}</span></td>
                    </tr>
                    </tbody>
                    {if $ismultidomain}
                        <tfoot>
                        <tr>
                            <td><span class="add-line">+{$_LANG['add']}</span></td>
                        </tr>
                        </tfoot>
                    {/if}
                </table>
            </div>
        </div>
    {/if}
    {if $validationType != 'dv'}
        <div id="organizationPart"  class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>{$_LANG['organization_info']}</p></div>
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
</div>

<input type="hidden" name="countries" id="countries" value='{$countries}'>
<input type="hidden" name="productCode" id="productCode" value={$productCode}>
<input type="hidden" name="maxdomain" id="maxdomain" value={$maxdomain}>
<input type="hidden" name="iswildcard" id="iswildcard" value={$iswildcard}>
<input type="hidden" name="ismultidomain" id="ismultidomain" value={$ismultidomain}>
<input type="hidden" name="validationType" id="validationType" value={$validationType}>
<input type="hidden" name="configData" id="configData" value='{$configData}'>
<input type="hidden" name="other" id="other" value='{$other}'>
<input type="hidden" name="sslType" id="sslType" value='{$sslType}'>
<input type="hidden" name="lang" id="lang" value='{$_LANG_JSON}'>

<div class="footDiv">
    <button class="submit-button" >{$_LANG['submit']}</button>
</div>

{literal}
    <script type="text/javascript">
        function deleteDomainTr(e){
            e.parents('tr').remove();
            initDomainNumber();
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
        function initDomainNumber(){
            $(".domainInput tbody tr").each(function (index,value) {
                var number = index + 1;
                $(this).find(".domainNumber").text(number)
            })
        }
        function checkIpUrl(ip){
            var ip = ip || '';
            if(!ip){
                return false;
            }
            var rep = /^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/;
            if(rep.test(ip)){
                return true;
            }
            return false;
        }
        function checkWildUrl(wildUrl){
            var wildUrl = wildUrl || '';
            if(!wildUrl){
                return false;
            }
            var rep = /^(?=^.{3,255}$)((\*))(\.[a-zA-Z0-9\u4E00-\u9FA5][-a-zA-Z0-9\u4E00-\u9FA5]{0,62})(\.[a-zA-Z0-9\u4E00-\u9FA5][-a-zA-Z0-9\u4E00-\u9FA5]{0,62})+$/u;
            if(rep.test(wildUrl)){
                return true;
            }
            return false;
        }
        function checkNormalUrl(url){
            var url = url || '';
            if(!url){
                return false;
            }
            if(checkIpUrl(url)){
                return false;
            }
            var rep = /^(?=^.{3,255}$)(([a-zA-Z0-9\u4E00-\u9FA5][-a-zA-Z0-9\u4E00-\u9FA5]{0,62}))(\.[a-zA-Z0-9\u4E00-\u9FA5][-a-zA-Z0-9\u4E00-\u9FA5]{0,62})+$/u
            if(rep.test(url)){
                return true;
            }
            return false;
        }
        function checkDCVMethod(method) {
            var method = method || '';
            var dcvmethods = ['HTTP_CSR_HASH','CNAME_CSR_HASH','HTTPS_CSR_HASH']
            if(($.inArray(method, dcvmethods) == -1) && !checkEmail(method)){
                return false;
            }
            return  true;
        }
        function checkDomain(domain,supNormal,supIp,supWild) {
            var domain = domain || '';
            var supNormal = supNormal || '';
            var supIp = supIp || '';
            var supWild = supWild || '';
            supNormal = parseInt(supNormal);
            supIp = parseInt(supIp);
            supWild = parseInt(supWild);
            if(supNormal && checkNormalUrl(domain)){
                return  true
            }
            if(supWild && checkWildUrl(domain)){

                return true;
            }
            if(supIp && checkIpUrl(domain)){

                return true;
            }

            return  false;
        }
        function initDomainEmails(e,domain,langs,supporHttps){
            var originVal = e.val();
            var optionStr = '';

            //先判断ip 仅支持文件验证http https
            if(checkIpUrl(domain)){
                optionStr = '<option value="HTTP_CSR_HASH">'+langs.http_csr_hash+'</option>' +
                    '<option value="HTTPS_CSR_HASH">'+langs.https_csr_hash+'(https)</option>';
            }
            else {
                optionStr = '<option value="CNAME_CSR_HASH">'+langs.cname_csr_hash+'</option>';

                //不是通配符证书，添加http https
                if(!checkWildUrl(domain)){
                    optionStr += '<option value="HTTP_CSR_HASH">'+langs.http_csr_hash+'</option>';
                    //是否支持https
                    if (supporHttps === '1') {
                        optionStr += '<option value="HTTPS_CSR_HASH">'+langs.https_csr_hash+'(https)</option>';
                    }
                }

                //添加邮箱验证
                var stop = domain.indexOf('.') + 1
                var firstStart = domain.substring(0,stop);
                if(firstStart == '*.' || firstStart == 'www.'){
                    domain = domain.substr(stop);
                }
                var emails = getAdminEmails(domain,[])

                $.each(emails, function (index, obj) {
                    var newOption = "<option value='"+obj+"'>"+obj+"</option>"
                    optionStr +=newOption;
                });
            }

            //先删除原来的
            e.empty();
            e.append(optionStr);

            e.val(originVal);


        }
        var getAdminEmails = function(domain,emails){
            var domain = domain || '';
            var emails = emails || [];
            if(!domain){
                return emails;
            }
            var rep = /(?=.{3,255}$)[a-zA-Z0-9\u4E00-\u9FA5][-a-zA-Z0-9\u4E00-\u9FA5]{0,62}(\.[a-zA-Z0-9\u4E00-\u9FA5][-a-zA-Z0-9\u4E00-\u9FA5]{0,62})+$/u
            var repRes =rep.exec(domain);
            if(repRes){
                var adddomain = repRes[0];
                var addEmails = [
                    'admin@' + adddomain,
                    'administrator@' + adddomain,
                    'hostmaster@' + adddomain,
                    'postmaster@' + adddomain,
                    'webmaster@' + adddomain
                ];
                emails = emails.concat(addEmails);
                var newdaomain = adddomain.substr(adddomain.indexOf('.') + 1)
                return getAdminEmails(newdaomain,emails)
            }else{
                return emails;
            }
        }
        function checkEmail(email){
            var email = email || '';
            var rep =/^[_a-zA-Z0-9-\u4E00-\u9FA5]+(\.[_a-zA-Z0-9-\u4E00-\u9FA5]+)*@[a-zA-Z0-9-\u4E00-\u9FA5]+(\.[a-zA-Z0-9-\u4E00-\u9FA5]+)*(\.[a-zA-Z0-9\u4E00-\u9FA5]{2,})$/u;
            if(rep.test(email)){
                return true;
            }
            return  false;
        }
        function addDomainTr(domainName,dcvMethod,isOrigin,lang){
            var maxDomainCount = $("#maxdomain").val();
            var nowDomainCount = $(".domainInput tbody tr").length;
            if(nowDomainCount >= maxDomainCount){
                getPromptAlert(lang.overplus,'note');
                return false;
            }
            var domainName = domainName || '';
            var dcvMethod = dcvMethod || '';
            var isOrigin = isOrigin || 0;

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
            var operateTd = '<button name="deleteDomain" class="delete-domain">'+lang.delete+'</button>';
            operateTd = '<span class="domainVrified">'+lang.verified+'</span>' + operateTd

            var addLine = '<tr>\n' +
                '                    <td><span class="domainNumber">1</span></spa><input type="text" class="domainName" name="domainName" value="'+domainName+'" placeholder="'+lang.domain+'"></td>\n' +
                '                    <td>\n' +dcvMethodStr+
                '                    </td>\n' +
                '                    <td>\n' + operateTd +
                '                    </td>\n' +
                '                </tr>';
            $(".domainInput tbody").append(addLine);
            if(isOrigin){
                $(".domainVrified:last-child").show();
            }
            $("input[name='domainName']:last-child").blur(function () {
                var domainName = $(this).val();
                var others = $.parseJSON($("#other").val());
                var supportNormal = others.supportNormal;
                var supportIp = others.supportIp;
                var supportWild = others.supportWild;
                var configData = $("#configData").val();
                if(configData){
                    configData = JSON.parse(configData);
                    var verifieDomains = configData.originalDomains;
                    if($.inArray(domainName, verifieDomains) != -1){
                        $(this).parents('tr').find('.domainVrified').show();
                    }else{
                        $(this).parents('tr').find('.domainVrified').hide();
                    }
                }
                if(!checkDomain(domainName,supportNormal,supportIp,supportWild)){
                    $(this).addClass('validationInputTip')
                }else{
                    $(this).removeClass('validationInputTip')
                }
            })
            var addSelect = $("select[name='dcvMethod']:last-child").val(dcvMethod).select2();
            addSelect.on('select2:opening',function () {
                var domain = $(this).parents('tr').find("[name='domainName']").val();
                initDomainEmails($(this),domain,lang,others.supportHttps)
            })
            addSelect.on('select2:close',function () {
                var method = $(this).val();
                if(checkDCVMethod(method)){
                    $(this).next('.select2-container').find(".select2-selection--single").removeClass('validationInputTip')
                }else{
                    $(this).next('.select2-container').find(".select2-selection--single").addClass('validationInputTip')
                }
            })
            addBeforLen++ ;
            $(".domainInput tbody tr:last-child").find(".domainNumber").text(addBeforLen)
            $(".domainInput tbody tr:last-child").find(".delete-domain").click(function () {
                deleteDomainTr($(this))
            })


        }
        function initDatas(lang){
            try{
                var configData = $("#configData").val();
                if(configData){
                    configData = JSON.parse(configData);

                    if(configData.csr){
                        $("#csr").val(configData.csr);
                        $("#csr").show();
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
                    if(configData.domainInfo ){
                        var verifieDomains = configData.originalDomains;
                        var keys = Object.keys(configData.domainInfo);
                        if(keys.length > 0){
                            var nowDomainCount = $(".domainInput tbody tr").length;
                            var domainNumber = 1;
                            $.each(configData.domainInfo,function (index,value) {
                                if(domainNumber <= nowDomainCount){
                                    var trnumber = domainNumber - 1;
                                    $(".domainInput tbody tr").eq(trnumber).find("[name='domainName']").val(value.domainName);
                                    if($(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").find("option[value='"+value.dcvMethod+"']").length > 0){
                                        $(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").val(value.dcvMethod).select2();
                                    }else{
                                        var otherOptionStr = "<option value='"+value.dcvMethod+"'>"+value.dcvMethod+"</option>";
                                        $(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").append(otherOptionStr).val(value.dcvMethod).select2();
                                    }
                                    if($.inArray(value.domainName, verifieDomains) != -1){
                                        $(".domainInput tbody tr").eq(trnumber).find('.domainVrified').show();
                                    }else{
                                        $(".domainInput tbody tr").eq(trnumber).find('.domainVrified').hide();
                                    }
                                    domainNumber++;
                                }else{
                                    var isOrigin = 0;
                                    if($.inArray(value.domainName, verifieDomains) != -1){
                                        isOrigin = 1;
                                    }
                                    addDomainTr(value.domainName,value.dcvMethod,isOrigin,lang)
                                }
                            })


                        }


                    }

                }

            }catch (e) {
                return false;
            }
        }
        $(function (){
            $("#submitData").click(function () {
                if (!confirm(lang.confirm_info_and_submit + "?")) {
                    return false;
                }

                resWindowShow(0);

                var domainInfo = [];
                var oneJson = '';
                $("#domains tr").each(function (index, element) {
                    var domainName = $(this).find("[name='domainName']").val();
                    if (domainName) {
                        oneJson = {
                            "domainName": domainName,
                            "dcvMethod": $(this).find("[name='domainVerify']").val(),
                            "dcvEmail": $(this).find("[name='verifyEmail']").val()
                        };
                        domainInfo.push(oneJson)
                    }

                });
                var orgInfo = {
                    "organizationName": $("#organizationName").val(),
                    "organizationAddress": $("#organizationAddress").val(),
                    "organizationCity": $("#organizationCity").val(),
                    "organizationCountry": $("#organizationCountry").val(),
                    "organizationPostCode": $("#organizationPostCode").val(),
                    "organizationMobile": $("#organizationMobile").val(),
                };
                var id = getQueryVariable("id");
                var data = {
                    "csr": $("#csr").val(),
                    "domainInfo": domainInfo,
                    "organizationInfo": orgInfo,
                };
                // var dataStr = JSON.stringify(data);
                $.ajax({
                    url: "clientarea.php?action=productdetails&id=" + id + "&step=replacessl",
                    type: "post",
                    data: {"data": data, "id": id},
                    timeout: 30000,
                    success: function (data) {
                        data = $.parseJSON(data);
                        //console.log(data);return false;
                        windowHide();
                        /*if (data.status == 0) {
                            swal({
                                title: data.status,
                                text: data.msg,
                                timer: 2000,
                                showConfirmButton: true
                            });
                            return false;
                        }*/
                        if (data.status != 1) {
                            $("#msg div").text('');
                            $("#msg div").append("<p>"+data.msg+"</p>");

                            $("#msg").show();
                        } else {
                            windowHide();
                            resWindowShow(1);
                            window.location.href = "clientarea.php?action=productdetails&id=" + id + "&step=index";

                        }
                        return false;
                    },
                    error: function (data) {
                        var json = JSON.parse(data.responseText);
                        console.log(json)
                    }
                });
            });
            var configData = $("#configData").val();
            var others =  $("#other").val();
            var others  =  $.parseJSON(others);
            var lang = $("input[name='lang']").val();
            lang = $.parseJSON(lang);

            if($("select[name='organizationCountry']").length > 0){
                var countries = $.parseJSON($("#countries").val());
                var select2Countries = [];
                $.each(countries, function (index, obj) {
                    var newcountryJson = {"id":obj.code,"text":obj.name}
                    select2Countries.push(newcountryJson)
                })
                $("select[name='organizationCountry']").select2({
                    'data':select2Countries,
                    'placeholder': lang.please_choose
                });
                $("select[name='organizationCountry']").on('select2:close',function () {
                    var value = $(this).val();
                    if((value == '') || (value == null) || (value == undefined)){
                        $(this).next('.select2-container').find(".select2-selection--single").addClass('validationInputTip')
                    }else{
                        $(this).next('.select2-container').find(".select2-selection--single").removeClass('validationInputTip')
                    }
                });
            }
            $("select[name='dcvMethod']").select2();
            $("select[name='dcvMethod']").on('select2:opening',function () {
                var domain = $("input[name='domainName']").val();
                initDomainEmails($(this),domain,lang,others.supportHttps)
            })
            $("select[name='dcvMethod']").on('select2:close',function () {
                var method = $(this).val();
                if(checkDCVMethod(method)){
                    $(this).next('.select2-container').find(".select2-selection--single").removeClass('validationInputTip')
                }else{
                    $(this).next('.select2-container').find(".select2-selection--single").addClass('validationInputTip')
                }
            })

            $("input[name='domainName']").blur(function () {
                var domainName = $(this).val();
                var supportNormal = others.supportNormal;
                var supportIp = others.supportIp;
                var supportWild = others.supportWild;

                var configData = $("#configData").val();
                if(configData){
                    configData = JSON.parse(configData);
                    var verifieDomains = configData.originalDomains;
                    if($.inArray(domainName, verifieDomains) != -1){
                        $(this).parents('tr').find('.domainVrified').show();
                    }else{
                        $(this).parents('tr').find('.domainVrified').hide();
                    }
                }
                if(!checkDomain(domainName,supportNormal,supportIp,supportWild)){
                    $(this).addClass('validationInputTip')
                }else{
                    $(this).removeClass('validationInputTip')
                }
            });
            $("#organizationPart input:visible").blur(function () {
                var value = $(this).val();
                if((value == '') || (value == null) || (value == undefined)){
                    $(this).addClass('validationInputTip');
                }else{
                    $(this).removeClass('validationInputTip')
                }
            });

            var titlenumber = 1;
            $(".titlenumber:visible").each(function(){
                $(this).text(titlenumber);
                titlenumber ++;
            })
            initDatas(lang);
            $("#csr").mouseout(function () {
                var ssltype = $("#sslType").val();
                if(ssltype == 'website_ssl'){
                    //解析csr
                    var csr = $(this).val();
                    if(csr){
                        var id = getQueryVariable("id");
                        $.ajax({
                            url: "clientarea.php?action=productdetails&id=" + id + "&step=decodeCsr",
                            type: "post",
                            data: {"csr": csr},
                            timeout: 30000,
                            success: function (data) {
                                data = $.parseJSON(data);
                                if (data.status == 0) {
                                    if(data.msg){
                                        $("#csr").addClass('validationInputTip');
                                        $(".csrTipError").text(data.msg);
                                        $(".csrTipError").show();
                                    }
                                    return  false;
                                }
                                var primaryDomain = $(".domainInput tbody tr").eq(0).find("[name='domainName']").val();
                                var csrdata = data.data;
                                var isMulti = $("#ismultidomain").val();
                                var validationTye = $("#validationType").val();
                                var supportIp = others.supportIp;
                                if(primaryDomain){
                                    var isMultiPositive = (validationTye =='dv') && isMulti && supportIp;
                                    if (!isMultiPositive) {
                                        if(primaryDomain != csrdata.CN){
                                            $("#csr").addClass('validationInputTip');
                                            $(".csrTipError").text(lang.must_same_pmain);
                                            $(".csrTipError").show();
                                            return false;
                                        }
                                    }
                                    $("#csr").removeClass('validationInputTip');
                                    $(".csrTipError").hide();
                                    return  false;
                                }else{
                                    $(".domainInput tbody tr").eq(0).find("[name='domainName']").val(csrdata.CN).trigger('blur');
                                    $(".domainInput tbody tr").eq(0).find("[name='dcvMethod']").val("").select2();
                                    $("#csr").removeClass('validationInputTip');
                                    $(".csrTipError").hide();
                                    return  false;

                                }

                            },
                            error: function (data) {


                            }
                        });
                    }else{

                    }
                }
            })
            $(".add-line").click(function () {
                addDomainTr('','','',lang);
            })

            $(".submit-button").unbind('click').click(function () {
                getAlert('confirm',lang.sure_to_submite,lang)
                $('.confirm').unbind('click').click(function () {
                    syalert.syhide('confirm');
                    getLoadBox('open');
                    //验证数据
                    $("#domainPart input").trigger('blur');
                    $("#organizationPart input").trigger('blur');
                    $("#domainPart select").trigger('select2:close')
                    $("#organizationPart select").trigger('select2:close');
                    if($(".validationInputTip").length > 0){
                        getLoadBox('close');
                        getPromptAlert(lang.params_error,'fail');
                        return false;
                    }
                    var domainInfo = [];
                    $(".domainInput tbody tr").each(function (index, element) {
                        var domainName = $(this).find("[name='domainName']").val();
                        if (domainName) {
                            var oneJson = {
                                "domainName": domainName,
                                "dcvMethod": $(this).find("[name='dcvMethod']").val(),
                            };
                            domainInfo.push(oneJson)
                        }
                    });
                    var orgInfo = {
                        "organizationName": $("#organizationPart [name='organizationName']").val(),
                        "organizationAddress": $("#organizationPart [name='organizationAddress']").val(),
                        "organizationCity": $("#organizationPart [name='organizationCity']").val(),
                        "organizationCountry": $("#organizationPart [name='organizationCountry']").val(),
                        "organizationState": $("#organizationPart [name='organizationState']").val(),
                        "organizationPostCode": $("#organizationPart [name='organizationPostCode']").val(),
                        "organizationMobile": $("#organizationPart [name='organizationMobile']").val(),
                    };
                    var id = getQueryVariable("id");
                    var csr = $("#csr").val();
                    var data = {
                        "csr": csr,
                        "domainInfo": domainInfo,
                        "organizationInfo": orgInfo
                    };

                    $.ajax({
                        url: "clientarea.php?action=productdetails&id=" + id + "&step=submitReplace",
                        type: "post",
                        data: {"data": data},
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
