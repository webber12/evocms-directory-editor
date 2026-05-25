<?php

namespace EvolutionCMS\EvoDirectoryEditor\Traits;

use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Render
{
    protected function render($view, $plh = [])
    {
        return view('DirectoryEditor::' . $view, $plh)->render();
    }
}
