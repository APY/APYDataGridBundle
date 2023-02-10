<?php

class ByPassFinalHook implements PHPUnit\Runner\BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        // mutate final classes into non final on-the-fly
        DG\BypassFinals::enable(); 
    }
}