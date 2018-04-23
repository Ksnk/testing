<?php
/**
 * Created by PhpStorm.
 * User: Аня
 * Date: 22.04.2018
 * Time: 15:36
 */

namespace Ksnk\testing\Apiupdater;

use Illuminate\Support\Facades\DB;

//use Illuminate\Database\Capsule\Manager as DB;
//use \Illuminate\Foundation\Application as App;

class doitall
{

    /**
     * Подготовка и выдача даных для курла
     * @param $api
     * @return array
     */
    private function prepare_data($api){
        if(empty($api)) {
            $apis = DB::select('select * from api');
        } else {
            $apis = DB::select('select * from api where id=?',[$api]);
        }
        $curldata = [];
        if(empty($apis)) return $curldata;

        // считаем данные и сочиняем задания для curl_multi_init все зараз
        foreach ($apis as $api) {
            $endpoints = explode(',', $api->endpoints);
            $eps = array_chunk($endpoints, $api->maxconn);

            foreach($eps as $_eps) {
                $data=http_build_query(['data'=>$_eps]);
                if ($api->type & 1) { // so it's GET'
                    $curldata[] =
                        [
                            'api' => $api->id,
                            'url' => $api->url . '?' . $data,
                            'type' => 'GET',
                            'respond' => '',
                            'code' => 0,
                        ];
                } else {
                    $curldata[] =
                        [
                            'api' => $api->id,
                            'url' => $api->url,
                            'type' => 'POST',
                            'data' => $data,
                            'respond' => '',
                            'code' => 0,
                        ];
                }
            }
        }
        return  $curldata;
    }

    /**
     * Единичный запуск курла для одной строчки записи.
     * @todo: Переделать на multy
     * @param $data
     * @param $xx
     */
    private function curl(&$data)
    {
        $url = $data['url'];//"http://mydomain.ru/api/metod/1/table";
        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url );
            if ($data['type']=='POST') { // so do a GET
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            $info = curl_getinfo($ch);
            $data['code']=$info['http_code'];
            $data['respond']=json_decode($result, true);
        } catch( \Exception $e){
            if(isset($info['http_code']))
                $data['code']=$info['http_code'];
            else
                $data['code']=-1;
            $data['respond']=[];
        }
        curl_close($ch);
    }

    function db_read( $api=null, callable $callback=null)
    {

        $curldata= $this->prepare_data($api);

       // $callback('debug',$curldata);

        // устанавливаем верхнюю планку прогрессбара
        $callback('total',count($curldata));

        foreach($curldata as &$data){
            $this->curl($data);
            //$callback('debug',$data);
            // сдвигаем прогрессбар
            $callback('+1');
        }
        unset($data);// just a dirty magikkk, don't mention it...

        // вписываем полученные данные в базу
        foreach($curldata as $data){
            if($data['code']!='200') continue;
            if(!is_array($data['respond'])) continue;
            // todo: какая то реакция на грязь в выводе нужна
            foreach($data['respond'] as $key=>$val){
                // $db_table->beginTransaction();
                $_data = [
                    'endpoint_id' => $key,
                    'type' => $data['api'],
                ];
                $_ep = DB::selectOne('select * from services where `type`=:type and `endpoint_id`=:endpoint_id', $_data);
                $_data['value'] = $val['value'];

                if (!$_ep) {
                    DB::insert('insert into services set value=:value,type=:type, endpoint_id=:endpoint_id,  created_at=now(), updated_at=now()', $_data);
                } else {
                    DB::update('update services set value=:value,  updated_at=now() where type=:type and endpoint_id=:endpoint_id', $_data);
                }
                // $db_table->commit();
            }
        }
    }

}