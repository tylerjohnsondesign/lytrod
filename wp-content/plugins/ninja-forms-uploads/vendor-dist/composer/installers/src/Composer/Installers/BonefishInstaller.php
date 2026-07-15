<?php

namespace NF_FU_VENDOR\Composer\Installers;

class BonefishInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('package' => 'Packages/{$vendor}/{$name}/');
}
