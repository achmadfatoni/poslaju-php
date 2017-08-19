<?php

class PoslajuTest extends \PHPUnit\Framework\TestCase
{
    public function testIsThereAnySyntaxError(){
        $var = new \Achmadfatoni\Poslaju\Poslaju();
        $this->assertTrue(is_object($var));
        unset($var);
    }

}