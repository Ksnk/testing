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

    function curl($data,$xx)
    {
        $url = $data->url;//"http://mydomain.ru/api/metod/1/table";
        $ch = curl_init();
        //$xx = explode(',', $data->endpoints);
        if (empty($xx)) return false;
        $xx='data[]=' . implode('&data[]=', $xx);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        if ($data->type & 1) { // so do a GET
            curl_setopt($ch, CURLOPT_URL, $url . '?'.$xx);
        } else { // so POST allowed
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xx);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close ($ch);
        return ['info'=> $info['http_code'], 'result'=>$result ];// $result;
    }

    function db_read()
    {
        // семаформи
       /* $key     = ftok(__FILE__,'m');
        $a        = sem_get($key);
        if($sema=sem_acquire($a, true)) {*/

            $apis = DB::select('select * from api');
            $results = [];
            //return $apis ;
            //$results['apis']=$apis;
            foreach ($apis as $api) {
                $endpoints = explode(',', $api->endpoints);
                $eps = array_chunk($endpoints, $api->maxconn);
                if (empty($eps)) continue;
                foreach ($eps as $ep) {
                    $result = $this->curl($api, $ep);
                    $results[] = $result;
                    $result = json_decode($result['result'], true);
                    if (!empty($result)) {
                        foreach ($result as $key => $val) {
                            // $db_table->beginTransaction();
                            $data = [
                                'endpoint_id' => $key,
                                'type' => $api->id,
                            ];
                            $_ep = DB::selectOne('select * from services where `type`=:type and `endpoint_id`=:endpoint_id', $data);
                            $data['value'] = $val['value'];

                            if (!$_ep) {
                                DB::insert('insert into services set value=:value,type=:type, endpoint_id=:endpoint_id,  created_at=now(), updated_at=now()', $data);
                            } else {
                                DB::update('update services set value=:value,  updated_at=now() where type=:type and endpoint_id=:endpoint_id', $data);
                            }
                            // $db_table->commit();
                        }
                    }
                }
            }
            // семафорим взад
 //           sem_release($sema);
 //       }
        return $results;
    }

}