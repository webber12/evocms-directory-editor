<?php

namespace EvolutionCMS\EvoDirectoryEditor\Traits;

use EvolutionCMS\EvoCatalogOptions\EvoCatalogOptionsHelper as Helper;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait Request
{

    public function setRequest($request = false) {
        $this->request = ($request === false) ? $_REQUEST : $request;
    }
    
    protected function getFromRequest($key, $type = false)
    {
        $out = false;
        if(request()->has($key)) {
            $tmp = request()->get($key);
            switch($type) {
                case 'string':
                    $tmp = (string)$tmp;
                    $tmp = trim(evo()->stripTags($tmp));
                    break;
                case 'int':
                    $tmp = (int)$tmp;
                    break;
                default:
                    break;
            }
            $out = $tmp;
        }
        return $out;
    }

    protected function getValuesFromRequest()
    {
        if(request()->has('field')) {
            $field = request()->get('field');
            return [ array_key_first($field), reset($field) ];
        } else {
            foreach(request()->all() as $k => $v) {
                if (preg_match('/tv(\d+)/', $k, $matches)) {
                    $tvId = $matches[1] ?? false;
                    if(!empty($tvId)) {
                        $tvname = SiteTmplvar::find($tvId)->name;
                        return [ $tvname, $v ];
                    }
                }
            }
        }
        return [];
    }

}
