# Openstore client

## Installation

### Via Composer (alternative 1)
Soluble components can be installed via composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```sh
php composer.phar require belgattitude/openstore-client:1.*
```


### Via github clone (alternative 2)

Clone in a directory

```sh
	cd my/project/dir
	git clone https://github.com/belgattitude/openstore-client.git .
	php composer.phar self-update
	php composer.phar install
```	

Create configuration files

 Copy ./config/oclient.config.php.dist in ./config/oclient.config.php

```sh
	cd ./config
	cp oclient.config.php.dist oclient.config.php
```	

 Edit base_url and api_key in the configuration file


## Updating

```sh
	cd my/project/dir
	git pull
	php composer.phar update
```

	
	