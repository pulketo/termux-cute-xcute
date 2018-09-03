WHAT IS
			Script to launch android apps by keywords.			
	
			This script is intended to be run on termux emulator (which is a Terminal emulator and Linux environment for Android)

			Requires (Android side):
				Termux (play store)
				Termux-Launhcer (https://github.com/amsitlab/termuxlauncher)
			Requires (Termux side)
				PHP (install with: pkg install php)

NAME
			xc -  command to execute an app searching by name

SYNOPSIS
			xc -f <searchterm1> [searchterm2] ...
			xc -l 

DESCRIPTION
			xc.phar is a php script to make easier to launch an app within the termux command line, 
			just run the script with some search terms, and if there is only one result, will execute that app
			 
			all human output goes to stderr and json output goes to stdout useful if you want to pipe 
			it to jq to prettyfy or extra processing.
			It's straighforward to use, if you have a suggestion, please send it to: pulketo at G.mail

EXAMPLES
			xc wha -f
				will launch and first app containing "wha" on its name, in my case will open whatsapp
			xc inbursa
				will launch an app called i-movil that app is called com.inbursa.client internally (i hate those apps whose names has nothing to do with what they actually do)
			xc lite
				in my case, will show two apps that contain the name lite (messenger-lite and facebook-lite)
			xc .lite 
				will launch facebook lite

OPTIONS
      Search terms must not have a dash before the word, it's a bug/feature on the library nategood/commando

   General options
			-f --firstmatch
					No matter if there is more than one app with that name, execute the first on the list.
			-V 	--version
					Show version
			-h --help Show basic usage


EXIT STATUS
       0      Successful program execution.

       1      Too many matches, but -f not specified.

       2      No matches.

       3      Something weird happened

HISTORY
			2018 - Pulketo ;)
