<?php

namespace NF_FU_VENDOR\Composer\Installers;

class SyliusInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('theme' => 'themes/{$name}/');
}
