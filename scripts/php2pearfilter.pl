#!/usr/bin/perl -w

use strict;
use File::Find;

while(<>){
	 chomp;

	 s/class (.+) {/class $1\n\{/;
	 s/((\s+)(?:.*))function (.+) [{]/$1function $3\n$2\{/;
	 s/[(] /(/g;
	 s/ [)]/)/g;
	 s/[[] /[/g;
	 s/ []]/]/g;
	 s/  /    /g;
	 s/\t/    /g;
	 s/[!] /!/g;
	 s/(\s*)[{](?:\s*)[}]/$1\{\n$1\}\n/;

         print "$_\n";
}
