# Potenv 

This is a very simple library in php to read dotenv (.env) configuration and
setting the environment variables.

## Example

Create a .env file:
```sh
echo "MYVAR1=10\nMYVAR2=helloworld" > .env
```

Read and fill $_ENV but not overwriting existing variables:
```php

require_once('potenv.php');

// specify path to the .env
// This by defaults set only the $_ENV
usedotenv(__DIR__."/.env");

echo $_ENV['MYVAR1'].PHP_EOL;
echo $_ENV['MYVAR2'].PHP_EOL;
```

This outputs:
```
10
helloworld
```

## Custom

Read the dotenv file variables, specify path and a line separater (default '\n')
```php
$vars = dotenv(__DIR__."/.env", "\n");

echo $vars['MYVAR1'].PHP_EOL;
```

You can choose where to store the variables:
* DOTENV_STORE_ENV - Store in $_ENV (Default)
* DOTENV_STORE_SERVER - Store in $_SERVER
* DOTENV_STORE_PUTENV - Store with putenv/getenv
* DOTENV_STORE_ALL - All of the above

and also whether to overwrite or not. Here we choose to not overwrite existing environment.
```php
$overwrite = false;
store_dotenv($vars, DOTENV_STORE_ENV | DOTENV_STORE_SERVER, $overwrite);

echo $_ENV['MYVAR1'].PHP_EOL;
echo $_SERVER['MYVAR1'].PHP_EOL;
```



