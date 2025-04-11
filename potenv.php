<?php

/**
 * Read a dotenv (.env) file/string which has env-variables on each row.
 * @param mixed $envfile A filename or a string of content from a .env file.
 * @param mixed $line_sep A separator for each variable, default is newline.
 * @return mixed An assoc array of variables as key and string values.  
 */
function dotenv($envfile = ".env", $line_sep = "\n")
{
	$contents = "$envfile";
	if(is_file($envfile)) 
	{
		$contents = file_get_contents($envfile);
	}
	$lines = array_filter(explode($line_sep, $contents), function($line) {
		return strlen(trim($line)) > 0;
	});
	$map = array_reduce($lines, function($acc, $line) {
		$parts = explode("=", $line, 2);
		if(count($parts) > 1) 
		{
			$acc[strtoupper(trim($parts[0]))] = trim($parts[1]);
		}
		return $acc;
	}, []);
	return $map;
}

/**
 * Store dotenv into $_ENV
 */
const DOTENV_STORE_ENV = 1;

/**
 * Store dotenv into $_SERVER
 */
const DOTENV_STORE_SERVER = 2;

/**
 * Store dotenv into putenv/getenv
 */
const DOTENV_STORE_PUTENV = 4;

/**
 * Store dotenv into all stores: $_ENV, $_SERVER, putenv/getenv
 */
const DOTENV_STORE_ALL = DOTENV_STORE_ENV | DOTENV_STORE_SERVER | DOTENV_STORE_PUTENV;


/**
 * Store a variable map (from call to dotenv()) into one or several environments.
 * Default is the $_ENV.
 * @param mixed $dotenv The variable map from a call to dotenv().
 * @param mixed $store A bit mask to define which stores to save the variables into. 
 * Use bitwise or of DOTENV_STORE_* 
 * DOTENV_STORE_ENV - store in $_ENV (default)
 * DOTENV_STORE_SERVER - store in $_SERVER
 * DOTENV_STORE_PUTENV - store in putenv/getenv 
 * DOTENV_STORE_ALL - store in all of the above
 * @param bool $overwrite Set to true to overwrite any existing variables. Default is false.
 * @return void
 */
function store_dotenv($dotenv, $store=DOTENV_STORE_ENV, $overwrite=false)
{
	foreach($dotenv as $key => $value) {
		if((!isset($_ENV[$key]) || $overwrite) && ($store & DOTENV_STORE_ENV) > 0) {
			$_ENV[$key] = $value;
		}
		if((!isset($_SERVER[$key]) || $overwrite) && ($store & DOTENV_STORE_SERVER) > 0) {
			$_SERVER[$key] = $value;
		}
		if((getenv($key)===false || $overwrite) && ($store & DOTENV_STORE_PUTENV) > 0) {
			putenv("$key=$value");
		}
	}
}

/**
 * Load a .env file and store in $_ENV. Does not overwrite existing variables.
 * This is equal to: 
 * 
 * ```
 * <?php
 * store_dotenv(dotenv(".env", "\n"), DOTENV_STORE_ENV, false);
 * ?>
 * ```
 * 
 * @param mixed $envfile The path to .env file.
 * @return void
 */
function usedotenv($envfile = ".env")
{
	store_dotenv(dotenv($envfile), DOTENV_STORE_ENV, false);
}
