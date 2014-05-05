<?php

include_once(ROOT_PATH . '/php/common.php');

//$scan = shell_exec('scanimage -L');
//echo $scan;

if( !empty($config['scanner-device-id']))
{
	printf('<p>Using scanner: %1$s</p>', $config['scanner-device-id']);
}

?>
<form>
	<fieldset>
		<dl style="float:left; width:300px;">
			<dt>Fileame</dt>
			<dd><input type="text" class="text" name="filename" id="filename" value=""/></dd>
			
			<dt>File Format</dt>
			<dd>
				<select id="format" name="format">
					<option value="pdf">PDF</option>
					<option value="jpg">JPG</option>
					<option value="tiff">TIFF</option>
				</select>
			</dd>
			
			<!--dt>Destination</dt>
			<dd>
				<select style="float:left; display:block; clear:none; margin-right:1em;" id="destination" name="destination" onchange="document.getElementById('email').style.display=(this.value=='email' ? 'inline' : 'none');">
					<option value="download">Download</option>
					<option value="email">Email to</option>
				</select>
				<input type="text" id="email" class="text" value="" style="display:none; clear:none;"/>
			</dd-->
			
		</dl>
	</fieldset>
	<div>
		<input type="button" class="button1 lefty-button1" id="find-scanner-button" title="Find Scanner" onclick="find_scanner()" value="Find Scanner">
		<input type="button" class="button1 lefty-button1" id="scan-button" title="Scan Document" onclick="scan()" value="Scan">
	</div>
	<br class="clearfix"/>
</form>
<script type="text/javascript">
function scan()
{
	window.open("./action.php?action=scanner&mode=scan&format=" + $('#format').val() + "&filename=" + escape($('#filename').val()));
}
function find_scanner()
{
	$($('<div title="Scanner Discovery: ' + (new Date()) + '">Scanning...</div>').load("./action.php?action=scanner&mode=find-scanner")).dialog();
}
</script>