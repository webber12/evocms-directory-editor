<?php namespace EvolutionCMS\EvoDirectoryEditor\Controllers;

use EvolutionCMS\Models\SiteTmplvar;
use Pathologic\EvolutionCMS\MODxAPI\modResource;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\Schema;

class EvoDirectoryController
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
        $res = $doc->save();
        $save[] = [
            'id' => $id,
            'field' => $fieldName,
            'value' => $fieldValue,
            'res' => $res,
        ];
        return [ 'status' => 'success', 'request' => $this->request, 'save' => $save ];
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
                case 'radio':
                case 'dropdown':
                case 'multiselect':
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
