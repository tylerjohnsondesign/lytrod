<?php

namespace NF_FU_VENDOR\Composer\Installers;

class PPIInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('module' => 'modules/{$name}/');
}
