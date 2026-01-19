
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/syalert.min.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/honeySwitch.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/select.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/common.css" media="screen" rel="stylesheet" type="text/css">
    <link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/animate.min.css" media="screen" rel="stylesheet" type="text/css">
    <style>

        .bulk-add,.set-for-all{
            color: green;
            margin-left: 30px;
            font-size: 12px;
            cursor: pointer;
        }

        .main-content{
            background-color: #f8f8f8;
        }
        #renewornotPart{
            width: 100%;
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
            width: 100%;
            height: 100%;
        }
        #renewornotPart .rightselect{
            width: 30%;
            height: 100%;
            padding-top: 5px;
        }
        .rightselect label{
            font-weight: 500;
            cursor: pointer;
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
        .rightselect{
            float: right;
            text-align: right;
            font-size: 14px;
        }
        .rightselect .radioRight{
            margin-left: 20px;
            margin-right: 5px;
        }
        .rightselect .renewradio{
            vertical-align:middle;
            margin-right: 5px;
            margin-bottom: 6px;
        }
        .topTitle{
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
            display: none;
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
            width: 75px;
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
        {$_LANG['apply_des']}
    </div>
<div id="sslContent">
    {if $sslType == 'website_ssl'}
        <div id="renewornotPart" class="partpadding">
            <div class="leftTitle">
                <div class="title">
                    <span class="titlenumber">1</span>
                    <p style="display: inline-block">{$_LANG['is_renew']}</p>
                    <div class="rightselect">
                        <input type="radio" class="renewradio" name="isRenew" value="0" id="radio-renew-0" checked="checked"><label for="radio-renew-0">{$_LANG['is_renew_option_new']}</label>
                        <input type="radio" class="renewradio radioRight" name="isRenew" id="radio-renew-1" value="1"><label for="radio-renew-1">{$_LANG['is_renew_option_renew']}</label>
                    </div>
                </div>
                <div class="titleDescribe">
                    <span>{$_LANG['is_renew_des']}</span>
                </div>
            </div>

        </div>
        <div id="csrPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>CSR</p></div>
                <div class="topTitleDescribe">
                    <span>{$_LANG['csr_des']}</span>
                </div>
            </div>
            <div class="csrInput">
                <span class="csrInputTitle">{$_LANG['is_manual_csr']}</span>
                <span class="switch-off" themeColor="#0e5077" id="isManualCsr"></span>
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
                        <td>{$_LANG['domain']}{if $ismultidomain}<span class="bulk-add">{$maxdomain}</span>{/if}</td>

                        <td>{$_LANG['dcv_method']}{if $ismultidomain}<span class="set-for-all">{$_LANG['set_for_all']}</span>{/if}</td>

                        <td></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><span class="domainNumber">1</span>
                            <input type="text"class="domainName" placeholder="{$_LANG['domain']}" name="domainName"></td>
                        <td>
                            <select name="dcvMethod">
                                <option value="">{$_LANG['please_choose']}</option>
                                <option value="HTTP_CSR_HASH">{$_LANG['http_csr_hash']}</option>
                                <option value="CNAME_CSR_HASH">{$_LANG['cname_csr_hash']}</option>
                                <option value="HTTPS_CSR_HASH">{$_LANG['https_csr_hash']}</option>
                            </select>
                        </td>
                        <td></td>
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
        <div id="personalcontactPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>{$_LANG['contacts']}</p></div>
            </div>
            <div class="personalcontactInput">
                <table>
                    <tbody>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['organization_name']}</span><input type="text" name="adminOrganizationName"></td>
                        <td><span class="inputTitle">{$_LANG['title']}</span><input type="text" name="adminTitle" ></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['first_name']}</span><input type="text" name="adminFirstName"></td>
                        <td><span class="inputTitle">{$_LANG['last_name']}</span><input type="text" name="adminLastName"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['email_address']}</span><input type="text" name="adminEmail"></td>
                        <td><span class="inputTitle">{$_LANG['phone']}</span><input type="text" name="adminPhone"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['country']}</span>
                            <select name="adminCountry">
                            </select>
                        </td>
                        <td><span class="inputTitle">{$_LANG['address']}</span><input type="text" name="adminAddress"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['city']}</span><input type="text" name="adminCity"></td>
                        <td><span class="inputTitle">{$_LANG['province']}</span><input type="text" name="adminProvince"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['post_code']}</span><input type="text" name="adminPostCode"></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
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
                            <td><span class="inputTitle">{$_LANG['phone']}</span><input name="organizationMobile" type="text"></td>
                            <td><span class="inputTitle">{$_LANG['post_code']}</span><input name="organizationPostCode" type="text"></td>
                        </tr>
                        <tr>
                            <td>
                                <span class="inputTitle">{$_LANG['idType']}</span>
                                <select name="idType">
                                    <option value="TYDMZ">{$_LANG['organizationCode']}</option>
                                    <option value="OTHERS">{$_LANG['other']}</option>
                                </select>

                            </td>

                            <td><span class="inputTitle">{$_LANG['organizationRegNumber']}</span><input name="organizationRegNumber" type="text"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}
    {elseif $sslType == 'email_ssl'}
        <div id="csrPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>CSR</p></div>
                <div class="topTitleDescribe">
                    <span>{$_LANG['csr_des']}</span>
                </div>
            </div>
            <div class="csrInput">
                <span class="csrInputTitle">{$_LANG['is_manual_csr']}</span>
                <span class="switch-off" themeColor="#0e5077" id="isManualCsr"></span>
                <textarea id="csr"></textarea>
            </div>
        </div>
        <div id="personalcontactPart" class="partpadding">
            <div class="topTitle">
                <div class="title"><span class="titlenumber">1</span><p>{$_LANG['contacts']}</p></div>
            </div>
            <div class="personalcontactInput">
                <table>
                    <tbody>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['organization_name']}</span><input type="text" name="adminOrganizationName"></td>
                        <td><span class="inputTitle">{$_LANG['title']}</span><input type="text" name="adminTitle" ></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['first_name']}</span><input type="text" name="adminFirstName"></td>
                        <td><span class="inputTitle">{$_LANG['last_name']}</span><input type="text" name="adminLastName"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['email_address']}</span><input type="text" name="adminEmail"></td>
                        <td><span class="inputTitle">{$_LANG['phone']}</span><input type="text" name="adminPhone"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['country']}</span>
                            <select name="adminCountry">
                            </select>
                        </td>
                        <td><span class="inputTitle">{$_LANG['address']}</span><input type="text" name="adminAddress"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['city']}</span><input type="text" name="adminCity"></td>
                        <td><span class="inputTitle">{$_LANG['province']}</span><input type="text" name="adminProvince"></td>
                    </tr>
                    <tr>
                        <td><span class="inputTitle">{$_LANG['post_code']}</span><input type="text" name="adminPostCode"></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
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
                        <td><span class="inputTitle">{$_LANG['phone']}</span><input name="organizationMobile" type="text"></td>
                        <td><span class="inputTitle">{$_LANG['post_code']}</span><input name="organizationPostCode" type="text"></td>
                    </tr>
                    <tr>
                        <td>
                            <span class="inputTitle">{$_LANG['idType']}</span>
                            <select name="idType">
                                <option value="TYDMZ">{$_LANG['organizationCode']}</option>
                                <option value="OTHERS">{$_LANG['other']}</option>
                            </select>

                        </td>

                        <td><span class="inputTitle">{$_LANG['organizationRegNumber']}</span><input name="organizationRegNumber" type="text"></td>
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
<input type="hidden" name="configData" id="configData" value='{$configData}'>
<input type="hidden" name="other" id="other" value='{$other}'>
<input type="hidden" name="sslType" id="sslType" value='{$sslType}'>
<input type="hidden" name="lang" id="lang" value='{$_LANG_JSON}'>
<div id="submitPart"  class="partpadding">
    <button class="submit-button">{$_LANG['submit']}</button>
    <button class="draft-button">{$_LANG['save_draft']}</button>
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
        function addDomainTr(domainName,dcvMethod,langs){
            var maxDomainCount = $("#maxdomain").val();
            var nowDomainCount = $(".domainInput tbody tr").length;
            if(nowDomainCount >= maxDomainCount){
                getPromptAlert(langs.overplus,'note');
                return false;
            }
            var domainName = domainName || '';
            var dcvMethod = dcvMethod || '';
            var dcvMethodStr = '<select name="dcvMethod">\n' +
                '                            <option value="">'+langs.please_choose+'</option>\n' +
                '                            <option value="HTTP_CSR_HASH">'+langs.http_csr_hash+'</option>\n' +
                '                            <option value="CNAME_CSR_HASH">'+langs.cname_csr_hash+'</option>\n' +
                '                            <option value="HTTPS_CSR_HASH">'+langs.https_csr_hash+'</option> ';
            if(checkEmail(dcvMethod)){
                dcvMethodStr += '<option value="'+dcvMethod+'">'+dcvMethod+'</option>'
            }
            dcvMethodStr += '</select>';
            var addBeforLen =  $(".domainInput tbody tr:last-child").find(".domainNumber").text();
            var addLine = '<tr>\n' +
                '                    <td><span class="domainNumber">1</span></spa><input type="text" class="domainName" name="domainName" value="'+domainName+'" placeholder="'+langs.domain+'"></td>\n' +
                '                    <td>\n' +dcvMethodStr+
                '                    </td>\n' +
                '                    <td>\n' +
                '                        <button name="deleteDomain" class="delete-domain">'+langs.delete+'</button>\n' +
                '                    </td>\n' +
                '                </tr>';
            $(".domainInput tbody").append(addLine);
            $("input[name='domainName']:last-child").blur(function () {
                var domainName = $(this).val();
                var others = $.parseJSON($("#other").val());
                var supportNormal = others.supportNormal;
                var supportIp = others.supportIp;
                var supportWild = others.supportWild;

                if(!checkDomain(domainName,supportNormal,supportIp,supportWild)){
                    $(this).addClass('validationInputTip')
                }else{
                    $(this).removeClass('validationInputTip')
                }
            })
            var addSelect = $("select[name='dcvMethod']:last-child").val(dcvMethod).select2();
            var others =  $("#other").val();
            others  =  $.parseJSON(others);
            addSelect.on('select2:opening',function () {
                var domain = $(this).parents('tr').find("[name='domainName']").val();
                initDomainEmails($(this),domain,langs,others.supportHttps)
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
        function initDatas(langs){
            try{
                var configData = $("#configData").val();

                if(configData){
                    configData = JSON.parse(configData);
                    if(configData.csr){
                        honeySwitch.showOn("#isManualCsr");
                        $("#csr").val(configData.csr);
                        $("#csr").show();
                    }
                    if(parseInt(configData.originalfromOthers)){
                        $("input[name='isRenew'][value='1']").attr('checked',true)
                    }
                    if(configData.Administrator){
                        $("#personalcontactPart [name='adminOrganizationName']").val(configData.Administrator.organation);
                        $("#personalcontactPart [name='adminTitle']").val(configData.Administrator.job);
                        $("#personalcontactPart [name='adminFirstName']").val(configData.Administrator.firstName);
                        $("#personalcontactPart [name='adminLastName']").val(configData.Administrator.lastName);
                        $("#personalcontactPart [name='adminEmail']").val(configData.Administrator.email);
                        $("#personalcontactPart [name='adminPhone']").val(configData.Administrator.mobile);
                        $("#personalcontactPart [name='adminAddress']").val(configData.Administrator.address);
                        $("#personalcontactPart [name='adminCity']").val(configData.Administrator.city);
                        $("#personalcontactPart [name='adminProvince']").val(configData.Administrator.state);
                        $("#personalcontactPart [name='adminPostCode']").val(configData.Administrator.postCode)

                        var adminCountry = configData.Administrator.country; // 定义变量 selectedValue

                        $("select[name='adminCountry'] option[value='" + adminCountry + "']").prop('selected', true);
                    }
                    if(configData.organizationInfo){
                        $("#organizationPart [name='organizationName']").val(configData.organizationInfo.organizationName);
                        $("#organizationPart [name='organizationAddress']").val(configData.organizationInfo.organizationAddress);
                        $("#organizationPart [name='organizationCity']").val(configData.organizationInfo.organizationCity);
                        $("#organizationPart [name='organizationCountry']").val(configData.organizationInfo.organizationCountry);
                        $("#organizationPart [name='organizationState']").val(configData.organizationInfo.organizationState);
                        $("#organizationPart [name='organizationPostCode']").val(configData.organizationInfo.organizationPostCode);
                        $("#organizationPart [name='organizationMobile']").val(configData.organizationInfo.organizationMobile);

                        if (configData.organizationInfo.organizationCountry) {
                            // 安全地访问organizationCountry属性
                            var organizationCountry = configData.organizationInfo.organizationCountry;
                            $("select[name='organizationCountry'] option[value='" + organizationCountry + "']").prop('selected', true);
                        }

                        if (configData.organizationInfo.idType) {
                            // 安全地访问organizationCountry属性
                            var idType = configData.organizationInfo.idType;
                            $("select[name='idType'] option[value='" + idType + "']").prop('selected', true);
                        }

                        if (configData.organizationInfo.organizationRegNumber) {
                            // 安全地访问organizationCountry属性
                            $("#organizationPart [name='organizationRegNumber']").val(configData.organizationInfo.organizationRegNumber);
                        }
                    }
                    if(configData.domainInfo ){
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
                                    domainNumber++;
                                }else{
                                    addDomainTr(value.domainName,value.dcvMethod,langs)
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

            switchEvent("#isManualCsr",function(){
                $("#csr").show()
            },function(){
                $("#csr").hide()
            });

            var lang = $("input[name='lang']").val();
            lang = $.parseJSON(lang);

            var configData = $("#configData").val();
            var others =  $("#other").val();
            others  =  $.parseJSON(others);
            if($("select[name='organizationCountry']").length > 0){
                var countries = $.parseJSON($("#countries").val());

                var configData1 = JSON.parse(configData);

                if ('organizationInfo' in configData1 && 'organizationCountry' in configData1.organizationInfo) {
                    // organizationCountry 已被定义
                    var organizationCountry = configData1.organizationInfo.organizationCountry;
                } else {
                    // organizationCountry 未被定义
                    var organizationCountry = '12';
                }

                var select2Countries = [];

                $.each(countries, function (index, obj) {
                    if (obj.code === organizationCountry) {
                        var newcountryJson = {"id":obj.code,"text":obj.name}
                        select2Countries.push(newcountryJson)
                    }

                })

                $.each(countries, function (index, obj) {
                    if (obj.code !== organizationCountry) {
                        var newcountryJson = {"id":obj.code,"text":obj.name}
                        select2Countries.push(newcountryJson)
                    }
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
            $(".bulk-add").click(function () {
                $(".popout").show();
            })
            $(".popout-cancer").click(function(){
                $(".popout").hide();
            })
            $(".popout").click(function(e){
                if($(e.target).attr("class")==="popout-flex"){
                    $(".popout").hide();
                }
            })
            $(".popout-confirm").click(function (e) {
                var domainStr = $(".popout-input textarea").val();
                var domainArr = domainStr.split(/[\n\r]+/)
                var defaultDcvMethod = $("select[name='dcvMethod']").eq(0).val();
                var dcvEmailPrefix = '';
                if (checkEmail(defaultDcvMethod)){
                    var defaultDcvMethodArr = defaultDcvMethod.split("@");
                    defaultDcvMethod = 'EMAIL';
                    dcvEmailPrefix = defaultDcvMethodArr[0];
                    if (($.inArray(dcvEmailPrefix, ['admin','administrator','hostmaster','postmaster','webmaster']) == -1)){
                        dcvEmailPrefix = 'admin'
                    }
                }

                var others = $.parseJSON($("#other").val());
                var supportNormal = others.supportNormal;
                var supportIp = others.supportIp;
                var supportWild = others.supportWild;
                var errorDomain = [];


                $("input[name='domainName']").each(function (index,value) {
                    if (index != 0){
                        if (!$(this).val()){
                            deleteDomainTr($(this))
                        }
                    }
                })
                $.each(domainArr,function (index,value) {
                    if(!checkDomain(value,supportNormal,supportIp,supportWild)){
                        errorDomain.push(value)
                        return true;
                    }
                    var oneDcvmethod = ''
                    if (checkWildUrl(value)){
                        if (defaultDcvMethod == 'EMAIL'){
                            oneDcvmethod = dcvEmailPrefix + '@' + value.substr(2)
                        } else if(defaultDcvMethod =='CNAME_CSR_HASH'){
                            oneDcvmethod = 'CNAME_CSR_HASH';
                        }
                    } else if(checkIpUrl(value)){
                        if ((defaultDcvMethod == 'HTTPS_CSR_HASH') || (defaultDcvMethod == 'HTTP_CSR_HASH')){
                            oneDcvmethod = defaultDcvMethod;
                        }
                    } else {
                        if (defaultDcvMethod == 'EMAIL'){
                            if (value.substr(0,4) == 'www.'){
                                oneDcvmethod = dcvEmailPrefix + '@' + value.substr(4)
                            }else {
                                oneDcvmethod = dcvEmailPrefix + '@' + value
                            }
                        }else{
                            oneDcvmethod = defaultDcvMethod;
                        }

                    }
                    if (!$("input[name='domainName']").eq(0).val()) {
                        $("input[name='domainName']").eq(0).val(value);
                        if ($("select[name='dcvMethod']").eq(0).find("option[value='"+oneDcvmethod+"']").length > 0){
                            $("select[name='dcvMethod']").eq(0).val(oneDcvmethod).select2();
                        }else{
                            var otherOptionStr = "<option value='"+oneDcvmethod+"'>"+oneDcvmethod+"</option>";
                            $("select[name='dcvMethod']").eq(0).append(otherOptionStr).val(oneDcvmethod).select2();
                        }
                        return  true;
                    }
                    var result = addDomainTr(value,oneDcvmethod,lang)
                    if(result === false){
                        errorDomain.push(value)
                    }
                });
                if (errorDomain.length > 0){
                    $(".popout-input textarea").val(errorDomain.join("\r"))
                    $(".error-domain-note").show();
                    $(".popout-input textarea").addClass('error-border-note')
                }else {
                    $(".popout-input textarea").val("");
                    $(".popout").hide();
                    $(".error-domain-note").hide();
                    $(".popout-input textarea").removeClass('error-border-note')
                }
            })
            $(".set-for-all").click(function () {
                var defaultDcvMethod = $("select[name='dcvMethod']").eq(0).val();
                var dcvEmailPrefix = '';
                if (checkEmail(defaultDcvMethod)){
                    var defaultDcvMethodArr = defaultDcvMethod.split("@");
                    defaultDcvMethod = 'EMAIL';
                    dcvEmailPrefix = defaultDcvMethodArr[0];
                    if (($.inArray(dcvEmailPrefix, ['admin','administrator','hostmaster','postmaster','webmaster']) == -1)){
                        dcvEmailPrefix = 'admin'
                    }
                }



                $("select[name='dcvMethod']").not(":eq(0)").each(function (index,value) {
                    var domain = $(this).parents('tr').find("input[name='domainName']").val();

                    var oneDcvmethod = ''
                    if (checkWildUrl(domain)){
                        if (defaultDcvMethod == 'EMAIL'){
                            oneDcvmethod = dcvEmailPrefix + '@' + domain.substr(2)
                        } else if(defaultDcvMethod =='CNAME_CSR_HASH'){
                            oneDcvmethod = 'CNAME_CSR_HASH';
                        }
                    } else if(checkIpUrl(domain)){
                        if ((defaultDcvMethod == 'HTTPS_CSR_HASH') || (defaultDcvMethod == 'HTTP_CSR_HASH')){
                            oneDcvmethod = defaultDcvMethod;
                        }
                    } else if (checkNormalUrl(domain)) {
                        if (defaultDcvMethod == 'EMAIL'){
                            if (domain.substr(0,4) == 'www.'){
                                oneDcvmethod = dcvEmailPrefix + '@' + domain.substr(4)
                            }else {
                                oneDcvmethod = dcvEmailPrefix + '@' + domain
                            }
                        }else{
                            oneDcvmethod = defaultDcvMethod;
                        }

                    } else {
                        if (defaultDcvMethod != 'EMAIL') {
                            oneDcvmethod = defaultDcvMethod;
                        }
                    }

                    if ($(this).find("option[value='"+oneDcvmethod+"']").length > 0){
                        $(this).val(oneDcvmethod).select2();
                    }else{
                        var otherOptionStr = "<option value='"+oneDcvmethod+"'>"+oneDcvmethod+"</option>";
                        $(this).append(otherOptionStr).val(oneDcvmethod).select2();
                    }

                    if(checkDCVMethod(oneDcvmethod)){
                        $(this).next('.select2-container').find(".select2-selection--single").removeClass('validationInputTip')
                    }else{
                        $(this).next('.select2-container').find(".select2-selection--single").addClass('validationInputTip')
                    }


                })


            })


            if($("select[name='adminCountry']").length > 0){
                var countries = $.parseJSON($("#countries").val());

                var configData1 = JSON.parse(configData);

                if ('Administrator' in configData1 && 'country' in configData1.Administrator) {
                    // organizationCountry 已被定义
                    var adminCountry = configData1.Administrator.country;
                } else {
                    // organizationCountry 未被定义
                    var adminCountry = '123';
                }

                var select2Countries = [];
                $.each(countries, function (index, obj) {
                    if (obj.code === adminCountry) {
                        var newcountryJson = {"id":obj.code,"text":obj.name}
                        select2Countries.push(newcountryJson)
                    }
                })

                $.each(countries, function (index, obj) {
                    if (obj.code !== adminCountry) {
                        var newcountryJson = {"id":obj.code,"text":obj.name}
                        select2Countries.push(newcountryJson)
                    }
                })

                $("select[name='adminCountry']").select2({
                    'data':select2Countries,
                    'placeholder': lang.please_choose
                });

                $("select[name='adminCountry']").on('select2:close',function () {
                    var value = $(this).val();
                    // alert(value);
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

                    if(!checkDomain(domainName,supportNormal,supportIp,supportWild)){
                        console.log("error")
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

            $("#personalcontactPart input:visible").blur(function () {
                var value = $(this).val();
                var personalName = $(this).attr('name');
                if((value == '') || (value == null) || (value == undefined)){
                    $(this).addClass('validationInputTip');
                } else if(personalName == 'adminEmail'){
                    if(!checkEmail(value)){
                        $(this).addClass('validationInputTip');
                    }else{
                        $(this).removeClass('validationInputTip')
                    }
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
            $(".add-line").click(function () {
                addDomainTr('','',lang);
            })
            $(".draft-button").click(function () {
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
                    "idType": $("#organizationPart [name='idType']").val(),
                    "organizationRegNumber": $("#organizationPart [name='organizationRegNumber']").val(),
                };
                var adminInfo  = {
                    "organation":$("#personalcontactPart [name='adminOrganizationName']").val(),
                    "job":$("#personalcontactPart [name='adminTitle']").val(),
                    "firstName":$("#personalcontactPart [name='adminFirstName']").val(),
                    "lastName":$("#personalcontactPart [name='adminLastName']").val(),
                    "email":$("#personalcontactPart [name='adminEmail']").val(),
                    "mobile":$("#personalcontactPart [name='adminPhone']").val(),
                    "country":$("#personalcontactPart [name='adminCountry']").val(),
                    "address":$("#personalcontactPart [name='adminAddress']").val(),
                    "city":$("#personalcontactPart [name='adminCity']").val(),
                    "state":$("#personalcontactPart [name='adminProvince']").val(),
                    "postCode":$("#personalcontactPart [name='adminPostCode']").val(),
                }
                var id = getQueryVariable("id");
                var isRenew = $("input[name='isRenew']:checked").val();
                var csr = '';

                if($("#isManualCsr").hasClass('switch-on')){
                    csr = $("#csr").val();
                }

                var data = {
                    "server": 'other',
                    "csr": csr,
                    "domainInfo": domainInfo,
                    "organizationInfo": orgInfo,
                    "originalfromOthers": isRenew,
                    "Administrator":adminInfo
                };

                $.ajax({
                    url: "clientarea.php?action=productdetails&id=" + id + "&step=savedraft",
                    type: "post",
                    data: {"data": data},
                    timeout: 30000,
                    success: function (data) {
                        data = $.parseJSON(data);
                        if (data.status == 0) {
                            getwarning(data.error,lang)
                            return false;
                        }
                        getPromptAlert(lang.operate_suc,'success');
                        return false;

                    },
                    error: function (data) {
                        var json = JSON.parse(data.responseText);
                        getPromptAlert(lang.oprate_fail,'fail');
                        return false;

                    }
                });
            })
            $(".submit-button").unbind('click').click(function () {
                getAlert('confirm',lang.sure_to_submite,lang)
                $('.confirm').unbind('click').click(function () {
                    syalert.syhide('confirm');
                    getLoadBox('open');
                    //验证数据
                    $("#domainPart input").trigger('blur');
                    $("#organizationPart input").trigger('blur');
                    $("#personalcontactPart input").trigger('blur');
                    $("#domainPart select").trigger('select2:close')
                    $("#organizationPart select").trigger('select2:close');
                    $("#personalcontactPart select").trigger('select2:close');
                    if($(".validationInputTip").length > 0){
                        console.log(1234)
                        console.log($(".validationInputTip").length )
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
                        "idType": $("#organizationPart [name='idType']").val(),
                        "organizationRegNumber": $("#organizationPart [name='organizationRegNumber']").val(),
                    };
                    var adminInfo  = {
                        "organation":$("#personalcontactPart [name='adminOrganizationName']").val(),
                        "job":$("#personalcontactPart [name='adminTitle']").val(),
                        "firstName":$("#personalcontactPart [name='adminFirstName']").val(),
                        "lastName":$("#personalcontactPart [name='adminLastName']").val(),
                        "email":$("#personalcontactPart [name='adminEmail']").val(),
                        "mobile":$("#personalcontactPart [name='adminPhone']").val(),
                        "country":$("#personalcontactPart [name='adminCountry']").val(),
                        "address":$("#personalcontactPart [name='adminAddress']").val(),
                        "city":$("#personalcontactPart [name='adminCity']").val(),
                        "state":$("#personalcontactPart [name='adminProvince']").val(),
                        "postCode":$("#personalcontactPart [name='adminPostCode']").val(),
                    }
                    var id = getQueryVariable("id");
                    var isRenew = $("input[name='isRenew']:checked").val();
                    var csr = '';
                    if($("#isManualCsr").hasClass('switch-on')){
                        csr = $("#csr").val();
                    }
                    var data = {
                        "server": 'other',
                        "csr": csr,
                        "domainInfo": domainInfo,
                        "organizationInfo": orgInfo,
                        "originalfromOthers": isRenew,
                        "Administrator":adminInfo
                    };

                    $.ajax({
                        url: "clientarea.php?action=productdetails&id=" + id + "&step=applyssl",
                        type: "post",
                        data: {"data": data},
                        timeout: 30000,
                        success: function (data) {
                            data = $.parseJSON(data);
                            getLoadBox('close');
                            if (data.status === 0) {
                                getPromptAlert(lang.oprate_fail,'fail');
                                // getwarning(data.error,lang)
                                return false;
                            }
                            getPromptAlert(lang.operate_suc,'success');
                            window.location.reload();
                            return false;

                        },
                        error: function (data) {
                            getLoadBox('close');
                            var json = JSON.parse(data.responseText);
                            console.log(json)
                            getPromptAlert(lang.oprate_fail,'fail');
                            return false;

                        }
                    });
                })
            })


        })
    </script>
{/literal}
