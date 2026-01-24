<!DOCTYPE html>
<html>
<!-- head tag needs like blow -->
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="H8Qysv59zb3Si78KmifZwz36NNX6govKhqG7m59q">
{*    <link href="modules/servers/nicsrs_ssl/view/home/css/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">*}
    <link rel="stylesheet/less" type="text/css" href="modules/servers/nicsrs_ssl/view/home/css/common.css"/>
    <link rel="stylesheet/less" type="text/css" href="modules/servers/nicsrs_ssl/view/home/css/tipCss.css"/>
    <style>
        #content .left-part {
            width: 806px;
            float: left;
            background-color: white;
            margin-left: 20px;
            margin-bottom: 15px;
            padding-bottom: 20px;
        }

        .borderDiv {
            border-top: 1px solid #e4e4e4;
        }

        .commonDiv {
            padding-top: 10px;
            margin-bottom: 10px;
        }

        #server span {
            display: block;
            float: left;
            height: 40px;
            line-height: 40px;
            padding: 0 10px;
            border: 1px solid #E5E5E5;
            border-right: none;
        }

        #servers {
            width: 150px;
            height: 40px;
            border: 1px solid #E5E5E5;
        }

        .columDiv {
            width: 30px;
            float: left;
            line-height: 30px;
        }

        .commonfloatLeft {
            float: left;
        }

        .commonMarginleft {
            margin-left: 10px;
        }

        .addDomainDiv {
            text-align: center;
            color: #D69962;
            cursor: pointer;
        }

        .commontextarea {
            width: 100%;
            height: 300px;
            border: 1px solid #E5E5E5;
        }

        .helpdiv {
            float: right;
        }

        .helpdiv div {
            float: left;
            font-size: 12px;
            color: #0b58a2;
            margin-left: 10px;
        }

        .footDiv {
            margin-bottom: 100px;
        }

        .footDiv button {
            width: 100px;
            height: 40px;
            margin-left: 20px;
            border: none;
            color: white;
        }

        .firstbutton {
            background-color: #229cbe;
        }

        textarea {
            padding: 10px;
            outline: none;
        }

        input {
            padding-left: 10px;
            outline: none;
        }

        .twoLinesTable {
            width: 100%;
        }

        .twoLinesTable tr td {
            width: 50%;
        }

        .twoLinesTable tr td:nth-child(odd) span, .twoLinesTable tr td:nth-child(odd) input, .twoLinesTable tr td:nth-child(odd) select{
            float: left;
        }

        .twoLinesTable tr td:nth-child(even) span, .twoLinesTable tr td:nth-child(even) input, .twoLinesTable tr td:nth-child(even) select{
            float: right;
        }

        .twoLinesTable tr td span {
            min-width: 150px;
            padding: 0 10px;
            height: 34px;
            line-height: 34px;
            text-align: right;
            display: block;
        }

        .twoLinesTable tr td input {
            display: block;
        }

        .twoLinesTable tr td select {
            display: block;
        }

        #data-ov-ev-page #organization input, #data-ov-ev-page #organization select{
            width: 220px;
            height: 34px;
            border: 1px solid #E5E5E5;
            outline: none;
        }

        #data-ov-ev-page #organization tr {
            width: 100%;
        }

        #data-ov-ev-page #organization td {
            height: 50px;
        }

        #data-ov-ev-page #domains input {
            width: 200px;
            height: 34px;
            border: 1px solid #E5E5E5;
            margin-right: 10px;
            outline: none;
        }

        #data-ov-ev-page #domains select {
            width: 250px;
            height: 34px;
            border: 1px solid #E5E5E5;
            margin-right: 10px;
            outline: none;
        }

        #data-ov-ev-page #domains td {
            height: 50px;
            line-height: 25px;
        }

        #data-ov-ev-page h2 {
            margin: 0;
            font-size: 20px;
            font-weight: normal;
            line-height: 55px;
        }

        #data-ov-ev-page th {
            width: 100px;
            height: 30px;
            font-size: 14px;
            text-align: center;
            margin-right: 5px;
            vertical-align: middle;
            font-weight: normal;
        }

        .uploadDomain {
            min-width: 120px;
            height: 30px;
            background-color: #E5E5E5;
            border: none;
            float: right;
        }

        .domainsText {
            position: absolute;
            top: 0%;
            left: 0%;
            width: 100%;
            height: 100%;
            background-color: black;
            z-index: 100;
            opacity: .3;
        }

        .domainsInput {
            position: fixed;
            top: 10%;
            left: 50%;
            margin-left: -150px;
            width: 600px;
            height: 500px;
            color: black;
            font-size: 15px;
            padding: 20px;
            background-color: #fff;
            z-index: 999;
        }

        .domainsInput .domainsInputTitle {
            font-weight: bold;
        }

        .domainsInput textarea {
            width: 100%;
            margin-top: 20px;
            height: 80%;
            background-color: #F5F5F5;
            box-sizing: border-box;
            padding: 20px;
            color: #C1C1C1;
            border-radius: 3px;
        }

        .domainsInput .domainsInputFoot {
            margin-top: 30px;
            float: right;
        }

        .domainsInput .domainsInputFoot .domainsCancel {
            width: 75px;
            height: 25px;
            background-color: #DBDBDB;
            text-align: center;
            color: black;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .domainsInput .domainsInputFoot .domainsCancel {
            width: 75px;
            height: 25px;
            background-color: #DBDBDB;
            text-align: center;
            color: black;
            border: none;
            border-radius: 3px;
            margin-right: 15px;
            cursor: pointer;
        }

        .domainsInput .domainsInputFoot .domainsSubmit {
            width: 75px;
            height: 25px;
            background-color: #2B9EB9;
            text-align: center;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .domainsInput .errorDomainDiv {
            color: red;
            margin-bottom: -18px;
            font-size: 12px;
            font-weight: bold;
            display: none;
        }

        .deleteImg {
            cursor: pointer;
        }
    </style>
    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/less.min.js"></script>
{*    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/jquery-ui.min.js"></script>*}
{*    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/jquery.query-object.js"></script>*}
{*    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/jquery.dataTables.min.js"></script>*}
    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/base.js"></script>
    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/common.js"></script>
    <script type="text/javascript" src="modules/servers/nicsrs_ssl/view/home/js/manhua_msgTips.js"></script>

</head>
<script src="modules/servers/nicsrs_ssl/view/home/js/sweetalert.min.js"></script>
<body id="data-ov-ev-page">

<div id="content" style='min-width:800px;margin:0 auto'>
    <div class="left-part">
        <div class="commonDiv"><strong>{$_LANG['server_and_validate']}</strong></div>
        <div class="commonDiv borderDiv">
            <div class="commonDiv">
                <span>CSR</span>
                <div class="helpdiv">
                    <div><img src="modules/servers/nicsrs_ssl/view/home/image/link.png"><span><a
                                    href="{$visibleConfig['generate_csr_url']}"
                                    target="_blank">{$_LANG['generate_csr_file']}</a></span></div>
                </div>
                <div class="commonDiv textareaDiv">
                    <textarea class="commontextarea" id="csr">{$configData->csr}</textarea>
                </div>
                <span style="color: orangered">{$_LANG['csr_remarks']}</span>
            </div>
        </div>


    </div>
    <div class="left-part">
        <div class="commonDiv">
            <strong>{$_LANG['domain_validate']}</strong>
            {if $ismultidomain}
                <button class="uploadDomain">{$_LANG['batch_add_domains']}</button>
            {/if}
        </div>
        <div class="commonDiv borderDiv">
            <table id="domains">
                <tr>
                    <td>{$_LANG['your_domain']}</td>
                    <td>{$_LANG['domain_validate_method']}</td>
                    <td>{$_LANG['email_address']}</td>
                </tr>
                <tr>
                    <td>
                        <div class="columDiv" name="numberDomain">1.</div>
                        <input type="text" id="domainName" name="domainName" onfocus=this.blur() value="">
                    </td>
                    <td>
                        <select name="domainVerify" id="domainVerify">
                            <option value="">{$_LANG['please_choose_validate_method']}</option>
                            <option value="EMAIL">{$_LANG['email_validate']}</option>
                            <option value="HTTP_CSR_HASH">{$_LANG['file_validate']}</option>
                            <option value="CNAME_CSR_HASH">{$_LANG['dns_validate']}</option>
                            <option value="HTTPS_CSR_HASH">{$_LANG['file_validate']}(HTTPS)</option>
                        </select>
                    </td>
                    <td>
                        <select name="verifyEmail" id="verifyEmail">
                            <option value="">{$_LANG['please_choose_validate_email']}</option>
                        </select>
                    </td>
                </tr>
            </table>
            {if $ismultidomain}
                <div class="addDomainDiv" id="add_domain">
                    {$_LANG['more_domains']}＋
                </div>
            {/if}
        </div>
    </div>
    {if $validationType != 'dv'}
        <div class="left-part">
        <div class="commonDiv">
            <strong>{$_LANG['organization_contact_info']}</strong>
        </div>

        <div class="commonDiv borderDiv">
            <table id="organization" class="twoLinesTable">
                <tr>
                    <td><span>{$_LANG['org_name']}</span><input  type="text" id="organizationName" value=""/></td>
                    <td><input width="70%" type="text" id="organizationAddress" value=""/><span>{$_LANG['address']}</span></td>
                </tr>
                <tr>
                    <td><span>{$_LANG['city']}</span><input type="text" id="organizationCity" value=""/></td>
                    <td>
                        <select id="organizationCountry" name="organizationCountry" class="countryId">
                            <option value="">{$_LANG['please_choose_country']}</option>
                        </select>
                        <span>{$_LANG['country']}</span>
                    </td>
                </tr>
                <tr>
                    <td><span>{$_LANG['postal_code']}</span><input type="text" id="organizationPostCode" value=""/></td>
                    <td><input type="text" id="organizationMobile" value=""/><span>{$_LANG['phone']}</span></td>
                </tr>
            </table>
        </div>
    </div>
    {/if}
    <input type="hidden" name="countries" id="countries" value='{$countries}'>
    <input type="hidden" name="productCode" id="productCode" value={$productCode}>
    <input type="hidden" name="maxdomain" id="maxdomain" value={$maxdomain}>
    <input type="hidden" name="iswildcard" id="iswildcard" value={$iswildcard}>
    <input type="hidden" name="ismultidomain" id="ismultidomain" value={$ismultidomain}>
    <input type="hidden" name="configData" id="configData" value='{$configData}'>


    <div class="domainsPopup" style="display: none">
        <div class="domainsText">
        </div>
        <div class="domainsInput">
            <div class="domainsInputTitle">{$_LANG['multi_domain_bulk_import']}</div>
            <div class="errorDomainDiv">{$_LANG['domain_name_illegal']}</div>
            <textarea>{$_LANG['please_enter_domain_name']}</textarea>
            <div class="domainsInputFoot">
                <button class="domainsCancel">{$_LANG['cancel']}</button>
                <button class="domainsSubmit">{$_LANG['confirm']}</button>
            </div>
        </div>
    </div>
    <div id="msg" style="display: none;width: 100%;min-height: 40px;color: red;">
        <p style="font-size: 16px;font-weight: bold">{$_LANG['error_notice']}:</p>
        <div>

        </div>

    </div>
    <div style="clear:both"></div>
    <div class="footDiv">
        <button class="firstbutton" id="submitData">{$_LANG['submit']}</button>
    </div>
    <div style="clear:both"></div>
</div>
<input type="hidden" name="lang" value='{$_LANG_JSON}'>


<script>
    $(function () {
        $("#content").manhua_msgTips({
            timeOut: 4000,             //提示层显示的时间
            msg: [],            //显示的消息
            speed: 300,                //滑动速度
            type: "error"          //提示类型（1、success 2、error 3、warning）

        });

    })

    $(function () {
        $('#tabDownloads').remove();
        $('#tabAddons').remove();
        $('#tabChangepw').remove();
    })
</script>
{literal}
    <script type="text/javascript">
        function domainsChage(domainInput, lang) {
            var mydomain = domainInput.val();
            var mydomainReg = /(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/;
            var mydomainArr = mydomain.match(mydomainReg);
            if (!mydomainArr) {
                return false;
            }
            var mydomian = mydomainArr[0];

            var domainName = mydomian;
            if (!domainName) {
                return false;
            }
            domainInput.parents('tr').find("[name='verifyEmail'] option").remove()
            domainInput.parents('tr').find("[name='verifyEmail']").append("<option value=''>" + lang.please_choose_validate_email + "</option>");
            var nowProp = domainInput;

            $.ajax({

                url: "/modules/servers/nicsrs_ssl/interface.php",
                type: "post",
                data: {"domainName": domainName, 'type': 'email'},
                timeout: 30000,
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.status == 0) {
                        return false;
                    }

                    $.each(data.data, function (idx, obj) {
                        nowProp.parents('tr').find("[name='verifyEmail']").append("<option value='" + obj + "'>" + obj + "</option>");
                    })
                    return true;

                },
                error: function (data) {
                    var json = JSON.parse(data.responseText);
                    console.log(json)

                }
            });
        }

        $(function () {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': 'H8Qysv59zb3Si78KmifZwz36NNX6govKhqG7m59q'}
            });
            var lang = $("input[name='lang']").val();
            lang = $.parseJSON(lang);
            var countries = $.parseJSON($("#countries").val());
            $.each(countries, function (index, obj) {
                if ($("#country").val() == obj.code) {
                    $("#organizationCountry").append("<option style='width:220px' value='" + obj.code  + "' selected>" + obj.name + "</option>");
                } else {
                    $("#organizationCountry").append("<option style='width:220px' value='" + obj.code  + "'>" + obj.name + "</option>");
                }
            });

            $(document).on('change','[name=\'domainVerify\']', function(){
                if ($(this).val() == 'EMAIL') {
                    var obj = $(this).parents("tr").find("[name='domainName']");
                    var domainName = obj.val();
                    if (!domainName) {
                        return false;
                    }
                    obj.parents("tr").find("[name='verifyEmail'] option").remove();
                    var nowProp = obj.parents("tr").find("[name='verifyEmail']");
                    nowProp.append("<option value=''>" + lang.please_choose_validate_email + "</option>");
                    $.ajax({
                        url: "/modules/servers/nicsrs_ssl/interface.php",
                        type: "post",
                        data: {"domainName": domainName, 'type': 'email'},
                        timeout: 30000,
                        success: function (data) {
                            data = JSON.parse(data);
                            if (data.status == 0) {
                                return false;
                            }

                            $.each(data.data, function (idx, obj) {
                                nowProp.append("<option value='" + obj + "'>" + obj + "</option>");
                            });
                        },
                        error: function (data) {
                            var json = JSON.parse(data.responseText);
                            console.log(json);
                        }
                    });
                }
            })
            $(document).on('change','[name=\'domainName\']', function(){
                $(this).parents("tr").find("[name='domainVerify'] option").remove();
                $(this).parents("tr").find("[name='domainVerify']").append('<option value="">' + lang.please_choose_validate_method + '</option>');
                $(this).parents("tr").find("[name='domainVerify']").append('<option value="EMAIL">' + lang.email_validate + '</option>');
                $(this).parents("tr").find("[name='domainVerify']").append('<option value="HTTP_CSR_HASH">' + lang.file_validate + '</option>');
                $(this).parents("tr").find("[name='domainVerify']").append('<option value="CNAME_CSR_HASH">' + lang.dns_validate + '</option>');
                $(this).parents("tr").find("[name='domainVerify']").append('<option value="HTTPS_CSR_HASH">' + lang.file_validate + '(HTTPS)</option>');
                $(this).parents("tr").find("[name='domainVerify'] option[value='']").attr('selected', true);

                $(this).parents("tr").find("[name='verifyEmail'] option").remove();
                $(this).parents("tr").find("[name='verifyEmail']").append("<option value=''>" + lang.please_choose_validate_email + "</option>");
            });
            $(document).on('click','#domains [name=\'deleteImg\']', function(){
                $(this).parent().parent().parent().remove();
                getDomainNum();
            })


            var configData = $("#configData").val();
            console.log(configData);
            if (configData) {
                configData = JSON.parse(configData);
                if(configData.domainInfo){
                    $("#domainName").val(configData.domainInfo[0].domainName);
                    $("#domainVerify option[value='" + configData.domainInfo[0].dcvMethod + "']").attr('selected', true);
                    if(configData.domainInfo[0].dcvEmail){
                        $("#verifyEmail").append("<option value='"+ configData.domainInfo[0].dcvEmail +"' selected >" + configData.domainInfo[0].dcvEmail + "</option>")
                    }
                    //multi domain
                    if (configData.domainInfo.length > 1) {
                        for (i = 1; i < configData.domainInfo.length; i++) {
                            var dcvEmailStr = '';
                            if(configData.domainInfo[i].dcvEmail){
                                dcvEmailStr = '<option value="'+configData.domainInfo[i].dcvEmail+'" selected >' + configData.domainInfo[i].dcvEmail + '</option>';
                            }
                            $("#domains tbody").append('<tr data-no="' + i + '">\n' +
                                '                    <td><div class="columDiv" name="numberDomain">1.</div><input  type="text" name="domainName"  value="' + configData.domainInfo[i].domainName + '" ></td>\n' +
                                '                    <td><select class="commonfloatLeft" name="domainVerify"><option value="">' + lang.please_choose_validate_method + '</option><option value="EMAIL">' + lang.email_validate + '</option><option value="HTTP_CSR_HASH">' + lang.file_validate + '</option><option value="CNAME_CSR_HASH">' + lang.dns_validate + '</option><option value="HTTPS_CSR_HASH">' + lang.file_validate + '(HTTPS)</option></select></td>\n' +
                                '                    <td><div class="commonfloatLeft"><select name="verifyEmail"><option value="">' + lang.please_choose_validate_email + '</option>'+dcvEmailStr+'</select></div><div class="columDiv commonMarginleft"><img class="deleteImg" name="deleteImg" src="modules/servers/nicsrs_ssl/view/home/image/deletecha.png"></div></td>\n' +
                                '                </tr>');
                            getDomainNum();

                            //getEmailByVerify();

                            $("tr[data-no='" + i + "'] [name='domainVerify'] option[value='" + configData.domainInfo[i].dcvMethod + "']").attr('selected', true);
                        }

                        $("#domains [name='deleteImg']").click(function () {
                            $(this).parent().parent().parent().remove();
                            getDomainNum();
                        });
                    }
                }
                //isRenew


                //org
                if (configData.organizationInfo) {
                    $("#organizationName").val(configData.organizationInfo.organizationName);
                    $("#organizationCountry option[value='" + configData.organizationInfo.organizationCountry + "']").attr('selected', true);
                    $("#organizationState").val(configData.organizationInfo.organizationState);
                    $("#organizationCity").val(configData.organizationInfo.organizationCity);
                    $("#organizationAddress").val(configData.organizationInfo.organizationAddress);
                    $("#organizationPostCode").val(configData.organizationInfo.organizationPostCode);
                    $("#organizationMobile").val(configData.organizationInfo.organizationMobile);
                }
                //csr
                $("#csr").val(configData.csr);
                if(configData.privateKey){
                    $("input[name='privateKey']").val(configData.privateKey)
                }

            }

            var lang = $("input[name='lang']").val();
            lang = $.parseJSON(lang);

            $(".domainsInput textarea").focus(function () {
                if ($(this).val() == lang.please_enter_domain_name) {
                    $(this).val('');
                }
            });
            $(".domainsInput textarea").blur(function () {
                if ($(this).val() == '') {
                    $(this).val(lang.please_enter_domain_name);
                }
            });
            $(".uploadDomain").click(function () {
                $(".domainsPopup").css('display', 'block');
            });
            $(".domainsInput .domainsCancel").click(function () {
                $(".domainsPopup").css('display', 'none');
            });

            $(".domainsInput .domainsSubmit").click(function () {
                var domainStr = $(".domainsInput textarea").val();
                if (domainStr == '' || domainStr == lang.please_enter_domain_name) {
                    $(".domainsInput .errorDomainDiv").text(lang.please_enter_correct_domain);
                    $(".domainsInput .errorDomainDiv").css('display', 'block');
                    $(".domainsInput textarea").css('border-color', 'red');
                    return false;
                }

                $.ajax({
                    url: "/modules/servers/nicsrs_ssl/interface.php",
                    type: "post",
                    data: {"domains": domainStr, "type": 'validateDomains'},
                    timeout: 30000,
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status == 0) {
                            $(".domainsInput .errorDomainDiv").text(lang.please_enter_correct_domain);
                            $(".domainsInput .errorDomainDiv").css('display', 'block');
                            $(".domainsInput textarea").css('border-color', 'red');
                            return false;
                        }
                        if (data.data.suc) {
                            var sucDomains = data.data.suc;
                            var sucNum = 0;
                            var sucCount = sucDomains.length;
                            if ($("input[name=domainName][id!=domainName]").length != 0) {
                                $("input[name=domainName][id!=domainName]").each(function (index, value) {
                                    if (!$(this).val()) {
                                        if (sucNum < sucCount) {
                                            $(this).val(sucDomains[sucNum]);
                                            sucNum++;
                                        } else {
                                            return false;
                                        }
                                    }
                                });
                            }
                            if (sucNum < sucCount) {
                                for (var j = sucNum; j < parseInt($("#maxdomain").val()) - 1; j++) {
                                    add_one_domain(sucDomains[j])
                                }
                            }

                        }
                        if (data.data.fail) {
                            $(".domainsInput .errorDomainDiv").text(lang.domain_format_incorrect);
                            $(".domainsInput .errorDomainDiv").css('display', 'block');
                            $(".domainsInput textarea").css('border-color', 'red');
                            $(".domainsInput textarea").val(data.data.fail)
                        } else {
                            $(".domainsInput .errorDomainDiv").css('display', 'none');
                            $(".domainsInput textarea").val('');
                            $(".domainsInput textarea").css('border-color', '');
                            $(".domainsPopup").css('display', 'none');

                        }
                    },
                    error: function (data) {
                        var json = JSON.parse(data.responseText);
                        console.log(json)

                    }
                });
            });

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

            var domainsWidth = $("#domains").outerWidth();
            $("#add_domain").outerWidth(domainsWidth);

            function add_one_domain(domain = '') {
                var maxDomains = $("#maxdomain").val();
                var nowDomains = $("input[name=domainName]").length
                if (parseInt(nowDomains) >= parseInt(maxDomains)) {
                    return false;
                }
                if (domain) {
                    $("#domains tbody").append('<tr>\n' +
                        '                    <td><div class="columDiv" name="numberDomain">1.</div><input  type="text" name="domainName" onload="" value="' + domain + '" ></td>\n' +
                        '                    <td><select class="commonfloatLeft" name="domainVerify"><option value="">' + lang.please_choose_validate_method + '</option><option value="EMAIL">' + lang.email_validate + '</option><option value="HTTP_CSR_HASH">' + lang.file_validate + '</option><option value="CNAME_CSR_HASH">' + lang.dns_validate + '</option><option value="HTTPS_CSR_HASH">' + lang.file_validate + '(HTTPS)</option></select></td>\n' +
                        '                    <td><div class="commonfloatLeft"><select name="verifyEmail"><option value="">' + lang.please_choose_validate_email + '</option></select></div><div class="columDiv commonMarginleft"><img class="deleteImg" name="deleteImg" src="modules/servers/nicsrs_ssl/view/home/image/deletecha.png"></div></td>\n' +
                        '                </tr>');

                    getDomainNum();
                } else {
                    $("#domains tbody").append('<tr>\n' +
                        '                    <td><div class="columDiv" name="numberDomain">1.</div><input  type="text" name="domainName" value="' + domain + '" ></td>\n' +
                        '                    <td><select class="commonfloatLeft" name="domainVerify"><option value="">' + lang.please_choose_validate_method + '</option><option value="EMAIL">' + lang.email_validate + '</option><option value="HTTP_CSR_HASH">' + lang.file_validate + '</option><option value="CNAME_CSR_HASH">' + lang.dns_validate + '</option><option value="HTTPS_CSR_HASH">' + lang.file_validate + '(HTTPS)</option></select></td>\n' +
                        '                    <td><div class="commonfloatLeft"><select name="verifyEmail"><option value="">' + lang.please_choose_validate_email + '</option></select></div><div class="columDiv commonMarginleft"><img class="deleteImg" name="deleteImg" src="modules/servers/nicsrs_ssl/view/home/image/deletecha.png"></div></td>\n' +
                        '                </tr>');

                    getDomainNum();
                }

            }

            function getDomainNum() {
                $("#domains tr").each(function (index, element) {
                    if (index != 0) {
                        var num = index;
                        $(this).find("[name='numberDomain']").text(num + '.');
                    }
                });
            }

            $("#add_domain").click(function () {
                add_one_domain();
            });

            $("#csr").on('mouseout', function () {
                var csr = $("#csr").val();
                if (!csr) {
                    return false;
                }
                $.ajax({
                    url: "/modules/servers/nicsrs_ssl/interface.php",
                    type: "post",
                    data: {"csr": csr, 'type': 'csr'},
                    timeout: 30000,
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data.status == 0 || !data.data.data.CN) {
                            swal({
                                title: data.msg,
                                text: lang.two_seconds_auto_close,
                                timer: 2000,
                                showConfirmButton: false
                            })
                            return false;
                        }
                        if($("#domainName").val() == data.data.data.CN) {
                            return false;
                        }
                        $("#domainName").val(data.data.data.CN);

                        var domainName = data.data.data.CN;
                        if (!domainName) {
                            return false;
                        }
                        $("#verifyEmail option").remove()
                        $("#verifyEmail").append("<option value=''>" + lang.please_choose_validate_email + "</option>");
                        var nowProp = $("#verifyEmail");
                        $.ajax({
                            url: "/modules/servers/nicsrs_ssl/interface.php",
                            type: "post",
                            data: {"domainName": domainName, 'type': 'email'},
                            timeout: 30000,
                            success: function (data) {
                                data = JSON.parse(data);
                                if (data.status == 0) {
                                    return false;
                                }

                                $.each(data.data, function (idx, obj) {
                                    nowProp.append("<option value='" + obj + "'>" + obj + "</option>");
                                })

                                return true;

                            },
                            error: function (data) {
                                var json = JSON.parse(data.responseText);
                                console.log(json)

                            }
                        });
                        $("#domainName").trigger("change");
                        return false;

                    },
                    error: function (data) {
                        var json = JSON.parse(data.responseText);
                        console.log(json)

                    }
                });
            })
            $("#domainName").on('change', function () {
                $("#domainVerify").val('');
            });


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
        })

    </script>
{/literal}
</body>
</html>
