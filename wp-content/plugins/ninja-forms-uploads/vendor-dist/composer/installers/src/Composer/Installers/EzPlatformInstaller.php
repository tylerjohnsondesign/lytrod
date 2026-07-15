<?php

namespace NF_FU_VENDOR\Composer\Installers;

class EzPlatformInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('meta-assets' => 'web/assets/ezplatform/', 'assets' => 'web/assets/ezplatform/{$name}/');
}
