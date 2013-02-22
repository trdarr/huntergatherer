less:
	find src/less -name '*.less' -type f |\
		xargs -d "\n" -I f basename f .less |\
		xargs -d "\n" -I f lessc src/less/f.less www/css/f.css

