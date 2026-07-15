<?php

namespace NF_FU_VENDOR\Composer\Installers;

class PhpBBInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('extension' => 'ext/{$vendor}/{$name}/', 'language' => 'language/{$name}/', 'style' => 'styles/{$name}/');
}
