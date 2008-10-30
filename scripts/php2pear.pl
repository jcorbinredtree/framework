#!/usr/bin/perl -w

use strict;
use File::Find;

find(sub{
       return if !m/[.]php$/;

       my $data = '';

       open(IN, $File::Find::name) or die($File::Find::name . " read : $!");

       while (<IN>) {
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

	 $data .= "$_\n";
       }

       close (IN);

       open(OUT, '>', $File::Find::name) or die($File::Find::name . " write : $!");
       print OUT $data;
       close(OUT);
}, shift);
