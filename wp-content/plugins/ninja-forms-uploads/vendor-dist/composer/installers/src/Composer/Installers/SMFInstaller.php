<?php

namespace NF_FU_VENDOR\Composer\Installers;

class SMFInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('module' => 'Sources/{$name}/', 'theme' => 'Themes/{$name}/');
}
