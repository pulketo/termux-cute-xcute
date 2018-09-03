<?php
	/***
		Disclaimer: 
			This is a better launcher search by keywords autocompletable, but it fully depends/relies on Termux-Launcher (https://github.com/amsitlab/termuxlauncher) 
			which generates a file on: 			/sdcard/termuxlauncher/.apps-launcher, and it will only work with this file.
		*/
		DEFINE("VERSION", "v0.94");
		DEFINE("MAN", 
	"
	MANUAL PAGES
	");
	require __DIR__ ."/vendor/autoload.php";

	function fixEncoding($i){
		$t = array(
		  "\u00c0" =>"À",     "\u00c1" =>"Á",     "\u00c2" =>"Â",     "\u00c3" =>"Ã",     "\u00c4" =>"Ä",     "\u00c5" =>"Å",     "\u00c6" =>"Æ",     "\u00c7" =>"Ç",     "\u00c8" =>"È",     "\u00c9" =>"É",     "\u00ca" =>"Ê",     "\u00cb" =>"Ë",     "\u00cc" =>"Ì",     "\u00cd" =>"Í",     "\u00ce" =>"Î",     "\u00cf" =>"Ï",     "\u00d1" =>"Ñ",     "\u00d2" =>"Ò",     "\u00d3" =>"Ó",     "\u00d4" =>"Ô",     "\u00d5" =>"Õ",     "\u00d6" =>"Ö",     "\u00d8" =>"Ø",     "\u00d9" =>"Ù",     "\u00da" =>"Ú",     "\u00db" =>"Û",     "\u00dc" =>"Ü",     "\u00dd" =>"Ý",     "\u00df" =>"ß",     "\u00e0" =>"à",     "\u00e1" =>"á",     "\u00e2" =>"â",     "\u00e3" =>"ã",     "\u00e4" =>"ä",     "\u00e5" =>"å",     "\u00e6" =>"æ",     "\u00e7" =>"ç",     "\u00e8" =>"è",     "\u00e9" =>"é",     "\u00ea" =>"ê",     "\u00eb" =>"ë",     "\u00ec" =>"ì",     "\u00ed" =>"í",     "\u00ee" =>"î",     "\u00ef" =>"ï",     "\u00f0" =>"ð",     "\u00f1" =>"ñ",     "\u00f2" =>"ò",     "\u00f3" =>"ó",     "\u00f4" =>"ô",     "\u00f5" =>"õ",     "\u00f6" =>"ö",     "\u00f8" =>"ø",     "\u00f9" =>"ù",     "\u00fa" =>"ú",     "\u00fb" =>"û",     "\u00fc" =>"ü",     "\u00fd" =>"ý", "\u00ff" =>"ÿ");
		return strtr($i, $t);
	}

	function arrSearch($hayStack, $needle){
		$o = array_filter($hayStack, function($el) use ($needle) {
			return ( stripos($el['name'], $needle) !== false || stripos($el['cmd'], $needle) !== false );
		});
		return $o;
	}

	function trimapp($what){
		$o= trim($what, "() \t\n\r\0\x0B");	
		return $o;
	}

	function atrim($arr){
		$arr = array_map('trimapp', $arr);
		return $arr;
	}

	function getAppsInfo(){
		$appsTXT=trim(`(cat /sdcard/termuxlauncher/.apps-launcher | grep -B1 -e 'am start') || echo nok`);
		if (($appsTXT)=="nok")
			die("/sdcard/termuxlauncher/.apps-launcher not found, perhaps you haven't installed termux-launcher");
		$eachAppTXT=explode("--".PHP_EOL, $appsTXT);
		foreach($eachAppTXT as $k=>$v){
			$tmp = explode(PHP_EOL, $v);
			$appinfo = explode("|", $tmp[0]); $appinfo = atrim($appinfo);
			$appname = strtolower($appinfo[1]); // this should be already in lowercase, but who knows
			list($appcmd) = explode("&> /dev/null", trim($tmp[1])); //Extract the cmd, sorry for these ugly hacks, regex will do, but i'm so dumb on regex.
			$txt[]=array("name"=>$appname, "cmd"=>$appcmd);
		}
		return $txt;
		
	}
	function json_enc_show($arr){
		$o = json_encode($arr, JSON_PRETTY_PRINT);
		$o = fixEncoding($o);
		echo $o;
	}

	/**main**/
	$appsInfo=getAppsInfo();

	$opts = new Commando\Command();
	// define script switches and check them
	$opts->option('m')->aka('man')->
		describedAs(MAN)->boolean()->defaultsTo(false);
	$opts->option('l')->aka('list')->
		describedAs('list apps, this is the default, if you run the script without arguments, it will show the app list too')->boolean()->defaultsTo(false);
	$opts->option('f')->aka('firstmatch')->
		describedAs('Execute the first app on the list')->boolean()->defaultsTo(false);
	$opts->option('V')->aka('version')->
		describedAs(VERSION)->boolean()->defaultsTo(false);
	if ($opts['version']){
		echo VERSION.PHP_EOL;
		exit;
	}
	// has arguments?
	$noargs = ($opts[0] == null)?true:false;
	// No arguments
	if ($opts['list'] || $noargs ){
		fwrite(STDERR, "showing app list".PHP_EOL);
		json_enc_show($appsInfo);
		exit(0);
	}
	// check script arguments maximum 15 arguments...
	$numArgs=0;
	for ($i=0;$i<=15;$i++){
		if ($opts[$i]=="")
			break;
		// echo "$i:".$opts[$i].PHP_EOL;
		$numArgs=$i+1;
	}
	$o = $appsInfo;
	for($i=0;$i<$numArgs;$i++){
		$o = arrSearch($o, $opts[$i]);
	}
	switch(sizeOf($o)){
		case 0:
			fwrite(STDERR, "No matches, try less keywords or run without arguments to show full list".PHP_EOL);
			exit(2);
			// no matches
			break;
		case 1:
			$o = array_shift($o);
			fwrite(STDERR, "Found 1 Match, Launching...".PHP_EOL);
			$cmd = $o['cmd']." 2>&1";
			// echo "cmd:$cmd";			
			$response = explode(PHP_EOL, trim(`$cmd`));
			// $arrX = atrim($arrX);
			foreach($response as $k=>$v){
				$o['status'.$k] = $v;
			}
			json_enc_show($o, JSON_PRETTY_PRINT).PHP_EOL;
			break;
		default:
			// more than one match
			// verify -f
			if ($opts['firstmatch']){
				$o = array_shift($o);
				$o['status'] = "launched";
				fwrite(STDERR, "Launching 1st match...".PHP_EOL);
		  	$cmd = $o['cmd']." 2>&1";
			  // echo "cmd:$cmd";			
		  	$response = explode(PHP_EOL, trim(`$cmd`));
	  		// $arrX = atrim($arrX);
  			foreach($response as $k=>$v){
				  $o['status'.$k] = $v;
			  }
				json_enc_show($o, JSON_PRETTY_PRINT).PHP_EOL;
			}else{
				json_enc_show($o, JSON_PRETTY_PRINT).PHP_EOL;
				fwrite(STDERR, "Multiple matches found, but -f not specified".PHP_EOL);
			}
			break;
	}



