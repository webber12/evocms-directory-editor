<?php namespace EvolutionCMS\EvoDirectoryEditor\Controllers;

use EvolutionCMS\Models\SiteTmplvar;
use Pathologic\EvolutionCMS\MODxAPI\modResource;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\Schema;

class EvoDirectoryEditorController
{

    use \EvolutionCMS\EvoDirectoryEditor\Traits\Render;
    use \EvolutionCMS\EvoDirectoryEditor\Traits\Request;
    use \EvolutionCMS\EvoDirectoryEditor\Traits\Response;

    protected $params;
    protected $request;


    public function ajaxSaveValue()
    {
        $save = [];
        $params = $this->getValuesFromRequest();
        $id = $this->getFromRequest('id', 'int');

        if(empty($id) || empty($params) || count($params) != 2) {
            return [ 'status' => 'error', 'message' => 'invalid data' ];
        }

        $fieldName = $params[0];
        $fieldValue = $params[1];
        $fieldValue = $this->prepareValueBeforeSave($fieldValue, $fieldName);
        $doc = new modResource( evo() );
        $doc->edit($id);
        $doc->fromArray( [ $fieldName => $fieldValue ] );
        $res = $doc->save(true, true);
        $data = [
            'id' => $id,
            'field' => $fieldName,
            'value' => $fieldValue,
            'res' => $res,
        ];
        $doc->edit($id);
        $realValue = $doc->get($fieldName);
        $realValue = (string)(is_array($realValue) ? implode('||', $realValue) : $realValue);
        $data['value'] = $realValue;
        $data = $this->renderValue($data, SiteContent::find($id) );
        return [ 'status' => 'success', 'data' => $data, 'editor' => $this->parseEditorForm($id, $fieldName, $realValue) ];
    }


    public function ajaxGetEditor()
    {
        $id = $this->getFromRequest('id', 'int');
        $field = $this->getFromRequest('field', 'string');
        $doc = new modResource( evo() );
        $doc->edit($id);
        $value = $doc->get($field);
        $html = $this->parseEditorForm($id, $field, $value);

        return $this->response([ 'status' => 'success', 'value' => $value, 'field' => $field, 'html' => $html ]);

    }

    protected function renderValue($data, SiteContent $model)
    {
        $config = $this->loadDirectoryConfig($model->parent);
        $config['id'] = $model->parent;
        if(!empty($config['columns'][ $data['field'] ]['renderer'])) {
            $renderer = $config['columns'][ $data['field'] ]['renderer'];
            if(is_callable($renderer)) {
                $data['renderedValue'] = $renderer($data['value'], $model, $config);
            }
        } else {
            //default behavior for tv with elements
            if($this->isTv($data['field'])) {
                $tv = SiteTmplvar::where('name', $data['field']);
                if($tv->count() > 0) {
                    $tv = $tv->first();
                    $elements = $this->getTvElements($tv->elements);
                    if(!empty($elements)) {
                        $renderedValue = [];
                        foreach(explode('||', $data['value']) as $value) {
                            $renderedValue[] = $elements[$value] ?? $value;
                        }
                        $data['renderedValue'] = implode(', ', $renderedValue);
                    } else {
                        if(in_array($tv->type, [ 'checkbox', 'listbox-multiple' ])) {
                            $data['renderedValue'] = str_replace('||', ', ', $data['value']);
                        }
                    }
                }
            }
        }
        return $data;
    }

    protected function getTvElements($elements) {

        $arr = [];
        $tmp = ParseIntputOptions(ProcessTVCommand($elements, '', '', 'tvform'));
        foreach($tmp as $row) {
            $tmp2 = explode('==', $row);
            $key = $tmp2[1] ?? $tmp2[0];
            $arr[$key] = $tmp2[0];
        }
        return $arr;
    }

    protected function loadDirectoryConfig($parent)
    {
        foreach(glob(EVO_CORE_PATH . 'custom/directory/*.php') as $file) {
            $config = include($file);
            if(!empty($config['ids']) && in_array($parent, $config['ids'])) {
                return $config;
            }
        }

        return true;
    }

    protected function convertObjToArray($obj)
    {
        return json_decode(json_encode($obj), 1);
    }

    protected function parseEditorForm($id, $field, $value)
    {
        $html = '';
        $isTv = $this->isTv($field);
        $fieldType = 'text';
        if($isTv) {
            $res = SiteTmplvar::where('name', $field)->first()->toArray();
            $row = $this->convertObjToArray($res);
            if(is_array($value)) {
                $value = implode('||', $value); //fix for listbox-multiple
            }
            $fieldType = stripos($row['type'], 'custom_tv') === false ? $row['type'] : 'text';
            $rows = renderFormElement(
                $fieldType,
                $row['id'],
                $row['default_text'],
                $row['elements'],
                $value,
                '',
                $row,
                [],
                null,
                evo()->parseProperties($row['properties'], $row['name'], 'tv')
            );
            $rows = str_replace('onchange="documentDirty=true;"', ' ', $rows);
        }

        if (empty($rows)) {
            $rows = '';
            if (is_scalar($value)) {
                $rows .= $this->render('text', ['id' => $id, 'field' => $field, 'value' => $value]);
            } else {
                foreach ($value as $v) {
                    $rows .= $this->render('text', ['id' => $id, 'field' => $field, 'value' => $value]);
                }
            }
        }
        if(!empty($rows)) {
            $html = $this->render('wrapper', [ 'rows' => $rows, 'id' => $id, 'fieldType' => $fieldType ]);
        }
        return $html;
    }

    protected function getTvType($tvName)
    {
        return SiteTmplvar::where('name', $tvName)->first()->type;
    }

    protected function prepareValueBeforeSave($value, $field)
    {
        $isTv = $this->isTv($field);
        if($isTv) {
            $tvType = $this->getTvType($field);
            switch($tvType) {
                case 'checkbox':
                case 'listbox-multiple':
                    if(is_array($value)) {
                        $value = implode('||', $value);
                    }
                break;
                default:
                    break;
            }
        }
        return evo()->stripTags((string)$value);
    }

    protected function prepareTvValue($value, $tvType)
    {
        switch($tvType) {
            default:
                break;
        }
        return $value;
    }


    protected function isTv($field)
    {
        $model = new SiteContent();
        return !Schema::hasColumn($model->getTable(), $field);
    }


    protected function log($data, $subject = 'DirectoryEditor log', $type = 1)
    {
        evo()->logEvent(1, $type, '<pre>'.print_r($data, 1).'</pre>', $subject);
    }

}
