
recinfo::
	rsync -av --delete sdo2.local::records1/ rec_info1/
	rsync -av --delete sdo2.local::records2/ rec_info2/
	./get_rec_info.php -S 1 --sync | grep ADD || true
	./get_rec_info.php -S 2 --sync | grep ADD || true
