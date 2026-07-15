<?php

namespace NF_FU_VENDOR\Composer\Installers;

class AnnotateCmsInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('module' => 'addons/modules/{$name}/', 'component' => 'addons/components/{$name}/', 'service' => 'addons/services/{$name}/');
}
