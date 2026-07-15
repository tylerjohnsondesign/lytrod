<?php

namespace NF_FU_VENDOR\Composer\Installers;

class ItopInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('extension' => 'extensions/{$name}/');
}
