<?php

namespace NF_FU_VENDOR\Composer\Installers;

class KohanaInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('module' => 'modules/{$name}/');
}
