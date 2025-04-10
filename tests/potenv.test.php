<?php
require_once(__DIR__."/../potenv.php");


require_once(__DIR__."/../../pest/pest.php");

use function \pest\test;
use function \pest\expect;


// Set so one of the variables already exists
function init_test()
{
    $vars = ['MYVAR1', 'MYVAR2', 'MYVAR3'];
    foreach($vars as $var)
    {
        unset($_ENV[$var]);
        unset($_SERVER[$var]);
        // putenv("MYVAR=") set empty string and not unset
        // putenv("$var=");
        putenv("$var");
    }
    $_ENV['MYVAR3'] = -8;
    $_SERVER['MYVAR3'] = -8;
    // We do not set an initial value in putenv/getenv environment
    //putenv('MYVAR3=-8');
}

file_put_contents(__DIR__."/.env", "MYVAR1=10\nMYVAR2=helloworld\nMYVAR3=-1\n\n");
register_shutdown_function("unlink", __DIR__."/.env");



test("usedotenv() reads and sets \$_ENV but does not overwrite", function() {
    init_test();
    usedotenv(__DIR__."/.env");
    expect($_ENV)->toHaveKey('MYVAR1');
    // Test == where 10 == '10' is true
    expect($_ENV['MYVAR1'])->toBe(10);
    // Test strict equality ===
    // All read values are strings, so numerics are not converted 
    expect($_ENV['MYVAR1'])->not()->toBeEqual(10);
    expect($_ENV)->toHaveKey('MYVAR2');
    expect($_ENV['MYVAR2'])->toBe("helloworld");
    // Since by default variables are note overwritten, MYVAR3 has the initial value.
    expect($_ENV)->toHaveKey('MYVAR3');
    expect($_ENV['MYVAR3'])->not()->toBe(-1);
    expect($_ENV['MYVAR3'])->toBe(-8);
    // Non existing var
    expect($_ENV)->not()->toHaveKey('MYVARXYZ');

    // Check $_SERVER, it should not have anything other than the initial value
    expect($_SERVER)->not()->toHaveKey('MYVAR1');
    expect($_SERVER)->not()->toHaveKey('MYVAR2');
    // This is the initial value
    expect($_SERVER)->toHaveKey('MYVAR3');
    expect($_SERVER['MYVAR3'])->toBe(-8);
});


test("dotenv() reads variables", function() {
    init_test();
    $vars = dotenv(__DIR__."/.env");
    expect($vars)->toHaveCount(3);
    expect($vars)->toHaveKey('MYVAR1');
    // Test == where 10 == '10' is true
    expect($vars['MYVAR1'])->toBe(10);
    // Test strict equality ===
    // All read values are strings, so numerics are not converted 
    expect($vars['MYVAR1'])->not()->toBeEqual(10);
    expect($vars)->toHaveKey('MYVAR2');
    expect($vars['MYVAR2'])->toBe("helloworld");
    expect($vars)->toHaveKey('MYVAR3');
    expect($vars['MYVAR3'])->toBe(-1);
    expect($vars)->not()->toHaveKey('MYVARXYZ');
});

test("store_dotenv() stores variables, not overwriting", function() {
    init_test();

    $vars = dotenv(__DIR__."/.env");

    $overwrite = false;
    store_dotenv($vars, DOTENV_STORE_ENV | DOTENV_STORE_SERVER, $overwrite);

    // Check $_ENV
    expect($_ENV)->toHaveKey('MYVAR1');
    // Test == where 10 == '10' is true
    expect($_ENV['MYVAR1'])->toBe(10);
    // Test strict equality ===
    // All read values are strings, so numerics are not converted 
    expect($_ENV['MYVAR1'])->not()->toBeEqual(10);
    expect($_ENV)->toHaveKey('MYVAR2');
    expect($_ENV['MYVAR2'])->toBe("helloworld");
    // Since by default variables are note overwritten, MYVAR3 has the initial value.
    expect($_ENV)->toHaveKey('MYVAR3');
    expect($_ENV['MYVAR3'])->not()->toBe(-1);
    expect($_ENV['MYVAR3'])->toBe(-8);
    // Non existing var
    expect($_ENV)->not()->toHaveKey('MYVARXYZ');

    // Now check $_SERVER
    expect($_SERVER)->toHaveKey('MYVAR1');
    // Test == where 10 == '10' is true
    expect($_SERVER['MYVAR1'])->toBe(10);
    // Test strict equality ===
    // All read values are strings, so numerics are not converted 
    expect($_SERVER['MYVAR1'])->not()->toBeEqual(10);
    expect($_SERVER)->toHaveKey('MYVAR2');
    expect($_SERVER['MYVAR2'])->toBe("helloworld");
    // Since by default variables are note overwritten, MYVAR3 has the initial value.
    expect($_SERVER)->toHaveKey('MYVAR3');
    expect($_SERVER['MYVAR3'])->not()->toBe(-1);
    expect($_SERVER['MYVAR3'])->toBe(-8);
    // Non existing var
    expect($_SERVER)->not()->toHaveKey('MYVARXYZ');
});


test("store_dotenv() stores variables, overwriting = true", function() {
    init_test();

    $vars = dotenv(__DIR__."/.env");

    $overwrite = true;
    store_dotenv($vars, DOTENV_STORE_ENV | DOTENV_STORE_SERVER, $overwrite);

    // Since we overwrite we should get the value from the .env file
    expect($_ENV)->toHaveKey('MYVAR3');
    expect($_ENV['MYVAR3'])->toBe(-1);
    expect($_ENV['MYVAR3'])->not()->toBe(-8);

    expect($_SERVER)->toHaveKey('MYVAR3');
    expect($_SERVER['MYVAR3'])->toBe(-1);
    expect($_SERVER['MYVAR3'])->not()->toBe(-8);    
});


test("store_dotenv() stores in putenv/getenv, not overwriting", function() {
    init_test();

    $vars = dotenv(__DIR__."/.env");

    $overwrite = false;
    store_dotenv($vars, DOTENV_STORE_PUTENV, $overwrite);

    // $_ENV and $_SERVER should only have MYVAR3
    expect($_ENV)->not()->toHaveKey('MYVAR1');
    expect($_ENV)->not()->toHaveKey('MYVAR2');
    expect($_ENV)->toHaveKey('MYVAR3');
    expect($_SERVER)->not()->toHaveKey('MYVAR1');
    expect($_SERVER)->not()->toHaveKey('MYVAR2');
    expect($_SERVER)->toHaveKey('MYVAR3');

    // getenv returns === false when variable doesn't exist
    expect(getenv('MYVAR1'))->not()->toBeEqual(false);
    // Test == where 10 == '10' is true
    expect(getenv('MYVAR1'))->toBe(10);
    // Test strict equality ===
    // All read values are strings, so numerics are not converted 
    expect(getenv('MYVAR1'))->not()->toBeEqual(10);
    
    expect(getenv('MYVAR2'))->not()->toBeEqual(false);
    expect(getenv('MYVAR2'))->toBe("helloworld");
    expect(getenv('MYVAR3'))->not()->toBeEqual(false);
    // This is the value from .env, and we never set (putenv) an initial value in init_test
    expect(getenv('MYVAR3'))->toBe(-1);
    // Non existing var
    expect(getenv('MYVARXYZ'))->toBeEqual(false);   
});