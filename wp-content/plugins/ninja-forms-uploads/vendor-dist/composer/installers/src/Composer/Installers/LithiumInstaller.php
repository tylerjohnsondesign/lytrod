<?php

namespace NF_FU_VENDOR\Composer\Installers;

class LithiumInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('library' => 'libraries/{$name}/', 'source' => 'libraries/_source/{$name}/');
}
