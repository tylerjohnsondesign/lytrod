<?php

namespace NF_FU_VENDOR\Composer\Installers;

class DecibelInstaller extends BaseInstaller
{
    /** @var array */
    /** @var array<string, string> */
    protected $locations = array('app' => 'app/{$name}/');
}
