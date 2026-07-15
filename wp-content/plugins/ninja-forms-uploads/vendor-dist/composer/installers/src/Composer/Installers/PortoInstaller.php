<?php

namespace NF_FU_VENDOR\Composer\Installers;

class PortoInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('container' => 'app/Containers/{$name}/');
}
