
recinfo::
	rsync -av --no-perms --delete bbb2.guap.ru::records/ rec_info1/
	rsync -av --no-perms --delete bbb3.guap.ru::records/ rec_info2/
	rsync -av --no-perms --delete bbb4.guap.ru::records/ rec_info4/
	./get_rec_info.php -S 1 --sync | grep ADD || true
	./get_rec_info.php -S 2 --sync | grep ADD || true
	./get_rec_info.php -S 4 --sync | grep ADD || true
