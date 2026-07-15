<?php

namespace NF_FU_VENDOR\Composer\Installers;

class LaravelInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('library' => 'libraries/{$name}/');
}
