<?php

namespace NF_FU_VENDOR\Composer\Installers;

class CiviCrmInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('ext' => 'ext/{$name}/');
}
