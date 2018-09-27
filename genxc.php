<?php
	$srcRoot = __DIR__."/";
	$buildRoot = __DIR__."";
	$phar = new Phar($buildRoot . "/xc.phar", FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "xc.phar");
	$phar->startBuffering();
 	$phar->buildFromDirectory($srcRoot);
// 	$phar->compress(Phar::GZ);
 	$defaultStub=$phar->createDefaultStub("src/xc.php");
	$phar->setStub("#!/data/data/com.termux/files/usr/bin/php\n".$defaultStub);
	$phar->stopBuffering();
