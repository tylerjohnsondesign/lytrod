<?php

namespace NF_FU_VENDOR\Composer\Installers;

class MiaoxingInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('plugin' => 'plugins/{$name}/');
}
