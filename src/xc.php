<?php
	/***
		Disclaimer: 
			This is a better launcher search by keywords autocompletable, but it fully depends/relies on Termux-Launcher (https://github.com/amsitlab/termuxlauncher) 
			which generates a file on: 			/sdcard/termuxlauncher/.apps-launcher, and it will only work with this file.
		*/
		DEFINE("VERSION", "v0.94");
		DEFINE("MAXARGS", 15);
		DEFINE("APPLISTLOCATION", "/sdcard/termuxlauncher/.apps-launcher");

		DEFINE("MAN", 
	"
	MANUAL PAGES
	");
	require __DIR__ ."/../vendor/autoload.php";

	class termuxCuteXCute {
		// switches default
		private $debug = true;
		private $f = false; 		
		////////
	    public $appList;
	    public $searchKeywords=array();
		public function __construct(){
			$appsTXT=trim(`(cat /sdcard/termuxlauncher/.apps-launcher | grep -B1 -e 'am start') || echo nok`);
			if (($appsTXT)=="nok"){
				$this->status = "NOK";
				$this->error = APPLISTLOCATION." not found, perhaps you haven't installed termux-launcher";
				return false;
			}
			$eachAppTXT=explode("--".PHP_EOL, $appsTXT);
			foreach($eachAppTXT as $k=>$v){
				$tmp = explode(PHP_EOL, $v);
				$appinfo = explode("|", $tmp[0]); $appinfo = $this->atrim($appinfo);
				$appname = strtolower($appinfo[1]); // this should be already in lowercase, but who knows
				list($appcmd) = explode("&> /dev/null", trim($tmp[1])); //Extract the cmd, sorry for these ugly hacks, regex will do, but i'm so dumb on regex.
				$txt[]=array("name"=>$appname, "cmd"=>$appcmd);
			}
			$this->appList = $txt;
			// print_r($this->appList);exit;
			$this->status = "OK";
//			print_r($txt);
			return true;
		}

	private function fixEncoding($i){
		$t = array(
		  "\u00c0" =>"À",     "\u00c1" =>"Á",     "\u00c2" =>"Â",     "\u00c3" =>"Ã",     "\u00c4" =>"Ä",     "\u00c5" =>"Å",     "\u00c6" =>"Æ",     "\u00c7" =>"Ç",     "\u00c8" =>"È",     "\u00c9" =>"É",     "\u00ca" =>"Ê",     "\u00cb" =>"Ë",     "\u00cc" =>"Ì",     "\u00cd" =>"Í",     "\u00ce" =>"Î",     "\u00cf" =>"Ï",     "\u00d1" =>"Ñ",     "\u00d2" =>"Ò",     "\u00d3" =>"Ó",     "\u00d4" =>"Ô",     "\u00d5" =>"Õ",     "\u00d6" =>"Ö",     "\u00d8" =>"Ø",     "\u00d9" =>"Ù",     "\u00da" =>"Ú",     "\u00db" =>"Û",     "\u00dc" =>"Ü",     "\u00dd" =>"Ý",     "\u00df" =>"ß",     "\u00e0" =>"à",     "\u00e1" =>"á",     "\u00e2" =>"â",     "\u00e3" =>"ã",     "\u00e4" =>"ä",     "\u00e5" =>"å",     "\u00e6" =>"æ",     "\u00e7" =>"ç",     "\u00e8" =>"è",     "\u00e9" =>"é",     "\u00ea" =>"ê",     "\u00eb" =>"ë",     "\u00ec" =>"ì",     "\u00ed" =>"í",     "\u00ee" =>"î",     "\u00ef" =>"ï",     "\u00f0" =>"ð",     "\u00f1" =>"ñ",     "\u00f2" =>"ò",     "\u00f3" =>"ó",     "\u00f4" =>"ô",     "\u00f5" =>"õ",     "\u00f6" =>"ö",     "\u00f8" =>"ø",     "\u00f9" =>"ù",     "\u00fa" =>"ú",     "\u00fb" =>"û",     "\u00fc" =>"ü",     "\u00fd" =>"ý", "\u00ff" =>"ÿ");
		return strtr($i, $t);
	}

	private function arrSearch($hayStack, $needle){
		$o = array_filter($hayStack, function($el) use ($needle) {
			return ( stripos($el['name'], $needle) !== false || stripos($el['cmd'], $needle) !== false );
		});
		return $o;
	}

	private function trimapp($what){
		$o= trim($what, "() \t\n\r\0\x0B");	
		return $o;
	}

	private function atrim($arr){
		$arr = array_map(array($this, 'trimapp'), $arr);
		return $arr;
	}

	private function jsonEnc($arr){
		$o = json_encode($arr, JSON_PRETTY_PRINT);
		$o = $this->fixEncoding($o);
		return $o;
	}

	private function showJsonEnc($arr){
		print_r($this->jsonEnc($arr));
	}

	public function showDebug($what){
		if($this->debug){
			echo ">-debug--".PHP_EOL;
			echo $what;
			echo "--debug-<".PHP_EOL;			
		}
	}

	public function searchFor($what){
		$this->searchKeywords[] = $what;
		$this->showDebug("searchFor searchKeywords:".print_r($this->searchKeywords, true));
	}

	public function setResponse($i){
		$this->response = $i;
	}
	public function getResponse(){
		return $this -> showJsonEnc($this->response, JSON_PRETTY_PRINT).PHP_EOL;
	}
	public function showResponse(){
		echo $this -> showJsonEnc($this->response, JSON_PRETTY_PRINT).PHP_EOL;
	}

	public function doSearch(){
		// if no search keywords show appList
		if(sizeOf($this->searchKeywords)==0){
//			$this->showMan(); // for example
			$this->showList(); // default 
			exit;
		}
		// if search keywords > 0
		$o = $this->appList;
		for($i=0;$i<sizeOf($this->searchKeywords);$i++){
			$o = $this->arrSearch($o, $this->searchKeywords[$i]);
		}
		$this->showDebug("doSearch:".print_r($o, true));
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
				$this->setResponse($o);
				break;
			default:
				// more than one match
				// verify -f
				if (@$this->firstMatch){
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
						$this->setResponse($o);
					}else{
						$this->setResponse($o);
						fwrite(STDERR, "Multiple matches found, but -f not specified".PHP_EOL);
					}
					break;
		}
		
	}

	public function showList(){
		echo $this->jsonEnc($this->appList);
	}

	public function setFirstMatch(){
		$this->firstMatch = true;
	}
		
	}
	


	/**main**/
	$opts = new Commando\Command();
	// define script switches and check them
	$opts->option('m')->aka('man')->
		describedAs(MAN)->boolean()->defaultsTo(false);
	$opts->option('u')->aka('update')->
		describedAs('update newly installed or deleted apps')->boolean()->defaultsTo(false);
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

	$cc = new termuxCuteXCute();

	
	if ($opts['update']){
		$cc -> setFirstMatch();
		$cc -> searchFor("termuxlauncher");
		$cc -> doSearch();
		$cc -> getResponse();
		echo "termuxlauncher launched, newly installed show be searchable, deleted won't appear anymore ".PHP_EOL;
		echo "you should execute ";
		exit;
	}
	
	for ($i=0;$i<=MAXARGS;$i++){
		if ($opts[$i]=="")
			break;
		// echo "$i:".$opts[$i].PHP_EOL;
		$numArgs=$i+1;
		$cc->searchFor($opts[$i]);
	}

	$cc -> doSearch();
	$cc -> showResponse();


