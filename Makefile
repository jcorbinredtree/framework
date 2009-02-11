framework-update: SITE/framework/.git
	git submodule update
	make -C SITE/framework update

SITE/framework/.git:
	git submodule init SITE/framework
