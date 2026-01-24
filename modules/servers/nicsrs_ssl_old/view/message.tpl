
	<link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/syalert.min.css" media="screen" rel="stylesheet" type="text/css">
	<link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/select.css" media="screen" rel="stylesheet" type="text/css">
	<link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/common.css" media="screen" rel="stylesheet" type="text/css">
	<link href="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/css/animate.min.css" media="screen" rel="stylesheet" type="text/css">

	<style>
		.main-content{
			background-color: #f8f8f8;
		}
		.validationInputTip{
			border-color: red !important;
		}
		#orderInfoPart{
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
		#operatorPart{
			width: 100%;
			height: auto;
			background-color: #ffffff;
			box-sizing: border-box;
		}
		#buttonPart{
			width: 100%;
			height: auto;
			border-top: 1px solid #e6e6e6;
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
		.partpadding{
			padding: 0px 25px 20px 25px;
		}
		.domainInput{
			font-size: 14px;
			color: #000000;
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
		.domainInput table{
			width: 100%;
		}
		.domainInput td{
			width: 36%;
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
			margin-right: 20px;
		}
		.domainUnVerified{
			color: red;
			font-size: 14px;
			height: 30px;
			line-height: 30px;
			display: inline-block;
			margin-right: 20px;
		}
		.domainVrified{
			color: #0e5077;
			font-size: 14px;
			height: 30px;
			line-height: 30px;
			display: inline-block;
			margin-right: 20px;
		}
		#verifyInfo label{
			margin-right: 50px;
			cursor: pointer;
		}
		#verifyInfo{
			width: 100%;
			box-sizing: border-box;
			border-top: 1px solid #e6e6e6;
			padding: 20px 0px;
		}
		.domainInput ul,.domainInput li{
			list-style: none;
			padding: 0px;
		}
		li{
			overflow: hidden;
		}
		.domainInput ul li .verify-info-list{
			display: flex;
			line-height: 25px;
		}
		.domainInput ul li span{
			display: inline-block;
			width: 70px;
			line-height: 25px;
		}
		.domainInput ul li p{
			margin: 0px;
		}
		.saveVerifyInfo, .requestVerifyInfo,.cancelApply{
			background-color: #0e5077;
			width: 115px;
			height: 35px;
			line-height: 35px;
			color: white;
			border: none;
			margin-right: 20px;
			cursor: pointer;
			border-radius: 5px;
		}
		.verifyInfo-file, .verifyInfo-dns{
			display: none;
		}
		.topTitle {
			padding-top: 20px;
		}
	</style>
	<script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/select2.js"></script>
	<script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/syalert.min.js"></script>
	<script type="text/javascript" src="{$WEB_ROOT}/modules/servers/nicsrs_ssl/view/home/js/common.js"></script>

	<h2>{$productCode}</h2>
	<div class="alert alert-info">
		{$_LANG['message_des']}
	</div>
<div id="sslbody">
	<h2>{$productCode}</h2>

	<div id="orderInfoPart" class="partpadding">
		<div class="topTitle">
			<div class="title"><span class="titlenumber">1</span><p>{$_LANG['order_info']}</p></div>
		</div>
		<div class="orderInfoInput">
			<span class="orderInfoDetail">{$_LANG['ca_order_id']}<span class="orderDetailContent" name="vendorId"></span></span>
			<span class="orderInfoDetail">{$_LANG['application_time']}<span class="orderDetailContent" name="applyTime"></span></span>
		</div>
	</div>
	{if $sslType == 'website_ssl'}
		<div id="domainPart" class="partpadding">
			<div class="topTitle">
				<div class="title"><span class="titlenumber">1</span><p><p>{$_LANG['domain_info']}</p></div>
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
						<td><span class="domainNumber">1</span>
							<input type="text" disabled="disabled" class="domainName" name="domainName" placeholder="{$_LANG['domain']}"></td>
						<td>
							<select name="dcvMethod">
								<option value="CNAME_CSR_HASH">{$_LANG['cname_csr_hash']}</option>
								<option value="HTTP_CSR_HASH">{$_LANG['http_csr_hash']}</option>
								<option value="HTTPS_CSR_HASH">{$_LANG['https_csr_hash']}</option>
							</select>
						</td>
						<td>

						</td>
					</tr>

					</tbody>
					<tfoot>
					<tr><td><button class="saveVerifyInfo">{$_LANG['save_verification']}</button><button class="requestVerifyInfo">{$_LANG['request_verification']}</button></td></tr>
					</tfoot>
				</table>
				<div id="verifyInfo">
					<input type="radio" id="domain-verinfo-file" name="domain-verinfo" value="HTTP_CSR_HASH" checked="checked">
					<label for="domain-verinfo-file">{$_LANG['file_verification_value']}</label>
					<input type="radio" id="domain-verinfo-dns" name="domain-verinfo" value="CNAME_CSR_HASH">
					<label for="domain-verinfo-dns">{$_LANG['dns_verification_value']}</label>
				</div>
				<ul>
					<li class="verifyInfo-file">
						<div class="verify-info-list" id="filename">
							<span>{$_LANG['filename']}</span>
							<p></p>
						</div>
						<div class="verify-info-list" id="fileContent">
							<span>{$_LANG['content']}</span>
							<p style="white-space: pre-line;"></p>
						</div>
						<div class="verify-info-list" id="filePath">
							<span>{$_LANG['path']}</span>
							<p></p>
						</div>
						<div class="verify-info-list" id="downTxt">
							<button>{$_LANG['down_txt']}</button>
						</div>
					</li>
					<li class="verifyInfo-dns">
						<div class="verify-info-list" id="dnshost">
							<span>{$_LANG['host']}</span>
							<p></p>
						</div>
						<div class="verify-info-list" id="dnsvalue">
							<span>{$_LANG['value']}</span>
							<p></p>
						</div>
						<div class="verify-info-list" id="dnstype">
							<span>{$_LANG['type']}</span>
							<p></p>
						</div>
					</li>
				</ul>
			</div>

		</div>
	{elseif $sslType == 'email_ssl'}
		<div id="domainPart" class="partpadding">
			<div class="topTitle">
				<div class="title"><span class="titlenumber">1</span><p><p>{$_LANG['email_info']}</p></div>
			</div>
			<div class="domainInput">
				<p>{$_LANG['email_wait_info']}</p>

			</div>

		</div>
	{/if}

	<input type="hidden" name="data" id="data" value='{$data}'/>
	<input type="hidden" name="serviceid" id="serviceid" value='{$serviceid}'/>
	<input type="hidden" name="remoteid" id="remoteid" value='{$remoteid}'/>
	<input type="hidden" name="status" id="status" value='{$status}'/>
	<input type="hidden" name="productCode" id="productCode" value='{$productCode}'/>
	<input type="hidden" name="collectData" id="collectData" value='{$collectData}'/>
	<input type="hidden" name="lang" id="lang" value='{$_LANG_JSON}'>
	<input type="hidden" name="other" id="other" value='{$other}'>
	<div id="operatorPart" class="partpadding">

		<button class="cancelApply">{$_LANG['cancel_application']}</button>

	</div>

</div>

{literal}
<script type="text/javascript">
	function deleteDomainTr(e,lang){
		getAlert('deleteDomain',lang.sure_to_delete);
		$("#deleteDomain .confirm").unbind('click').click(function () {
			syalert.syhide('deleteDomain');
			getLoadBox('open');
			var daomainName = e.parents('tr').find("[name='domainName']").val();
			var data = {"domainName": daomainName};
			var id = getQueryVariable("id");

			$.ajax({
				url: "clientarea.php?action=productdetails&id=" + id + "&step=removeMdc",
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
					e.parents('tr').remove();
					initDomainNumber();
					getPromptAlert(lang.operate_suc,'success');
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
	function addDomainTr(domainName,dcvMethod,isVerify,lang){
		var domainName = domainName || '';
		var dcvMethod = dcvMethod || '';
		var isVerify = isVerify || '';
		var addBeforLen = $(".domainInput tbody tr:last-child").find(".domainNumber").text();
		var dcvMethodStr = '<select name="dcvMethod">\n' +
				'                            <option value="">'+lang.please_choose+'</option>\n' +
				'                            <option value="HTTP_CSR_HASH">'+lang.http_csr_hash+'</option>\n' +
				'                            <option value="CNAME_CSR_HASH">'+lang.cname_csr_hash+'</option>\n' +
				 '                           <option value="HTTPS_CSR_HASH">'+lang.https_csr_hash+'(https)</option>';

		if(checkEmail(dcvMethod)){
			dcvMethodStr += '<option value="'+dcvMethod+'">'+dcvMethod+'</option>'
		}
		dcvMethodStr += '</select>';
		var optionTd = '';
		if(isVerify == 'verified'){
			optionTd =  '<span class="domainVrified">'+lang.verified+'</span>';
		}else{
			optionTd = '<button name="deleteDomain" class="delete-domain">'+lang.delete+'</button><span ' +
			'class="domainUnVerified">'+lang.un_verified+'</span>'
		}
		var addLine = '<tr>\n' +
				'                    <td><span class="domainNumber">1</span></spa><input type="text" disabled="disabled" class="domainName" name="domainName" value="'+domainName+'" placeholder="'+lang.domain+'"></td>\n' +
				'                    <td>\n' +dcvMethodStr+
		'                    </td>\n' +
		'                    <td>\n' + optionTd +
		'                    </td>\n' +
		'                </tr>';
		$(".domainInput tbody").append(addLine);
		var addSelect = $("select[name='dcvMethod']:last-child").val(dcvMethod).select2();
		var others = $.parseJSON($("#other").val());
		addSelect.on('select2:opening',function () {
			var domain = $(this).parents('tr').find("[name='domainName']").val();
			initDomainEmails($(this),domain,lang,others.supportHttps)
		})
		addBeforLen++ ;
		$(".domainInput tbody tr:last-child").find(".domainNumber").text(addBeforLen)
		$(".domainInput tbody tr:last-child").find(".delete-domain").click(function () {
			deleteDomainTr($(this))
		})
	}
	function checkDCVMethod(method) {
		var method = method || '';
		var dcvmethods = ['HTTP_CSR_HASH','CNAME_CSR_HASH','HTTPS_CSR_HASH']
		if(($.inArray(method, dcvmethods) == -1) && !checkEmail(method)){
			return false;
		}
		return  true;
	}
	function initDatas(lang){
		try{
			var configData = $("#data").val();
			var collectData = $("#collectData").val();
			collectData = JSON.parse(collectData);
			if(configData){
				configData = JSON.parse(configData);
				if(configData.domainInfo ){
					var keys = Object.keys(configData.domainInfo);
					if(keys.length > 0){
						var nowDomainCount = $(".domainInput tbody tr").length;
						var domainNumber = 1;
						var defaultDcvMethod = 'HTTP_CSR_HASH'
						$.each(configData.domainInfo,function (index,value) {
							if(domainNumber == 1){
								if(value.dcvMethod == 'CNAME_CSR_HASH'){
									defaultDcvMethod = 'CNAME_CSR_HASH';
								}
							}
							if(domainNumber <= nowDomainCount){
								var trnumber = domainNumber - 1;
								$(".domainInput tbody tr").eq(trnumber).find("[name='domainName']").val(value.domainName);
								if($(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").find("option[value='"+value.dcvMethod+"']").length > 0){
									$(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").val(value.dcvMethod).select2();
								}else{
									var otherOptionStr = "<option value='"+value.dcvMethod+"'>"+value.dcvMethod+"</option>";
									$(".domainInput tbody tr").eq(trnumber).find("[name='dcvMethod']").append(otherOptionStr).val(value.dcvMethod).select2();;
								}
								$(".domainInput tbody tr").eq(trnumber).find('td').eq(2).empty();
								var operatorTd = '';
								if(domainNumber == 1){
									if(value.is_verify == 'verified'){
										operatorTd = '<span class="domainVrified">'+lang.verified+'</span>'
									}else{
										operatorTd = '<span class="domainUnVerified">'+lang.un_verified+'</span>'
									}

								}else{
									if(value.is_verify == 'verified'){
										operatorTd = '<span class="domainVrified">'+lang.verified+'</span>'
									}else{
										operatorTd = '<button name="deleteDomain" class="delete-domain">'+lang.delete+'</button><span class="domainUnVerified">'+lang.un_verified+'</span>'
									}
								}
								$(".domainInput tbody tr").eq(trnumber).find('td').eq(2).append(operatorTd);
								domainNumber++;
							}else{
								addDomainTr(value.domainName,value.dcvMethod,value.is_verify,lang)
								domainNumber++;
							}
						})
						$(".domainUnVerified").parents('tr').find("select[name='dcvMethod']").on('select2:close', function () {
							var method = $(this).val();
							if(checkDCVMethod(method)){
								$(this).next('.select2-container').find(".select2-selection--single").removeClass('validationInputTip')
							}else{
								$(this).next('.select2-container').find(".select2-selection--single").addClass('validationInputTip')
							}
						})
						$("input[name='domain-verinfo'][value='"+defaultDcvMethod+"']").attr('checked',true);
						$("input[name='domain-verinfo'][value='"+defaultDcvMethod+"']").trigger('change');
					}


				}
				if(collectData.vendorId){
					$("[name='vendorId']").text(collectData.vendorId)
				}
				if(configData.applyReturn.applyTime){
					$("[name='applyTime']").text(configData.applyReturn.applyTime)
				}

			}
		}catch (e) {
			return false;
		}
	}
	$(function (){
		var lang = $("input[name='lang']").val();
		lang = $.parseJSON(lang);
		var others = $.parseJSON($("#other").val());

		$("select[name='dcvMethod']").select2();
		$("select[name='dcvMethod']").on('select2:opening',function () {
			var domain = $("input[name='domainName']").val();
			initDomainEmails($(this),domain,lang,others.supportHttps)
		})
		var titlenumber = 1;
		$(".titlenumber:visible").each(function(){
			$(this).text(titlenumber);
			titlenumber ++;
		})
		$("input[name='domain-verinfo']").change(function () {
			var dcvmethod = $(this).val();
			var collectData = $("#collectData").val();
			collectData = JSON.parse(collectData);
			$(".verifyInfo-file").hide();
			$(".verifyInfo-dns").hide();
			if(dcvmethod == 'HTTP_CSR_HASH'){
				$("#filename p").text(collectData.DCVfileName)
				$("#fileContent p").text(collectData.DCVfileContent)
				$("#filePath p").text('http://example.com/.well-known/pki-validation/'+ collectData.DCVfileName)
				$(".verifyInfo-file").show();
				$("#downTxt").click(function (){
					downloadFile(collectData.DCVfileName,collectData.DCVfileContent)
				});
			}else{
				$("#dnshost p").text(collectData.DCVdnsHost)
				$("#dnsvalue p").text(collectData.DCVdnsValue)
				$("#dnstype p").text(collectData.DCVdnsType)
				$(".verifyInfo-dns").show();
			}

		});
		initDatas(lang);
		$(".saveVerifyInfo").click(function () {
			getLoadBox('open');
			$("#domainPart select").trigger('select2:close')
			if($(".validationInputTip").length > 0){
				getLoadBox('close');
				getPromptAlert(lang.params_error,'fail');
				return false;
			}
			var domainInfo = [];
			$(".domainUnVerified").each(function (index, value){
				var daomainName = $(this).parents('tr').find("[name='domainName']").val()
				if (daomainName) {
					var oneJson = {
						"domainName": daomainName,
						"dcvMethod": $(this).parents('tr').find("[name='dcvMethod']").val(),
					};
					domainInfo.push(oneJson)
				}
			})
			var data = {"domainInfo": domainInfo};
			var id = getQueryVariable("id");
			$.ajax({
				url: "clientarea.php?action=productdetails&id=" + id + "&step=batchUpdateDCV",
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
					return false;

				},
				error: function (data) {
					getLoadBox('close');
					var json = JSON.parse(data.responseText);
					getPromptAlert(lang.oprate_fail,'fail');
					return false;

				}
			});

		});
		$(".requestVerifyInfo").click(function () {
			getLoadBox('open');
			$("#domainPart select").trigger('select2:close')
			if($(".validationInputTip").length > 0){
				getLoadBox('close');
				getPromptAlert(lang.params_error,'fail');
				return false;
			}
			var domainInfo = [];
			$(".domainUnVerified").each(function (index, value){
				var daomainName = $(this).parents('tr').find("[name='domainName']").val()
				if (daomainName) {
					var oneJson = {
						"domainName": daomainName,
						"dcvMethod": $(this).parents('tr').find("[name='dcvMethod']").val(),
					};
					domainInfo.push(oneJson)
				}
			})
			var data = {"domainInfo": domainInfo};
			var id = getQueryVariable("id");
			$.ajax({
				url: "clientarea.php?action=productdetails&id=" + id + "&step=batchUpdateDCV",
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
					return false;

				},
				error: function (data) {
					getLoadBox('close');
					var json = JSON.parse(data.responseText);
					getPromptAlert(lang.oprate_fail,'fail');
					return false;

				}
			});

		});
		$(".cancelApply").click(function () {
			getAlert('cancelApply',lang.sure_to_cancel,lang);
			$("#cancelApply .confirm").unbind('click').click(function () {
				syalert.syhide('cancelApply');
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
