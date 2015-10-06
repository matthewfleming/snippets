<?php

function niceFizzBuzz()
{
    for ($i = 1; $i <= 100; $i++) {
        $fizzy = ($i % 3 == 0);
        $buzzy = ($i % 5 == 0);
        if ($fizzy) {
            echo "Fizz";
        }
        if ($buzzy) {
            echo "Buzz";
        }
        echo ($fizzy || $buzzy) ? '' : $i, "\n";
    }
}

function hackyFizzBuzz()
{
    for ($i = 1; $i <= 100; $i++) {
        if ($f = $i % 3 == 0) echo 'Fizz';
        if ($b = $i % 5 == 0) echo 'Buzz';
        echo ($f || $b) ? '' : $i, "\n";
    }
}

function obfuscatedFizzBuzz()
{
    for($i=1;$i<101;$i++){if($f=$i%3==0)echo'Fizz';if($b=$i%5==0)echo'Buzz';echo($f||$b)?'':$i,"\n";}
}

/**
 * JS for(i=1;i<=100;i++){console.log(i%15?i%3?i%5?i:'Buzz':'Fizz':'FizzBuzz')}
 */
function obfuscatedFizzBuzz2()
{
    for($i=1;$i<101;$i++){echo$i%15?$i%3?$i%5?$i:'Buzz':'Fizz':'FizzBuzz',"\n";}
}

obfuscatedFizzBuzz2();
