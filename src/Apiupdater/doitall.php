<?php
/**
 * Created by PhpStorm.
 * User: Аня
 * Date: 22.04.2018
 * Time: 15:36
 */

namespace Ksnk\testing\Apiupdater;

use Illuminate\Support\Facades\DB;

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
     * @param $curldata
     * @param $callback
     * @todo: Подумать о переделке на динамическe. подставe обработанных реквестов... наверное проще побелить...
     */
    private function multycurl (&$curldata,$callback){
        do { // эта музыка будет вечна...

            $multi = curl_multi_init();
            $channels = [];
            $busy = [];

            foreach ($curldata as $key => $data) {
                if (isset($busy[$data['type']]) || $data['code'] != 0) continue;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $data['url']);
                if ($data['type'] == 'POST') { // so do POST
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                curl_multi_add_handle($multi, $ch);
                $channels[$key] = $ch;
                $busy[$data['type']] = true;
            }
            if (count($channels) == 0) break; // ... пока не кончатся необработанные строки

            $active = null;
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($multi) == -1) {
                    continue;
                }

                do {
                    $mrc = curl_multi_exec($multi, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            foreach ($channels as $key => $channel) {
                try {
                    $info=curl_getinfo ($channel);
                    $curldata[$key]['code']=$info['http_code'];
                    $curldata[$key]['respond'] = json_decode(curl_multi_getcontent($channel), true);
                } catch(\Exception $e){
                    if(isset($info['http_code']))
                        $curldata[$key]['code']=$info['http_code'];
                    else
                        $curldata[$key]['code']=-1;
                    $curldata[$key]['message']=$e->getMessage();
                }
                 curl_multi_remove_handle($multi, $channel);
                $callback('+1');
            }

            curl_multi_close($multi);
        } while(true); // ну а чо?

    }

    /**
     * наружный хандл, дергаем тута...
     * @param null $api - номер api или пусто
     * @param callable|null $callback - выдача наружу результата
     */
    public function db_read( $api=null, callable $callback=null)
    {

        $curldata= $this->prepare_data($api);

        // устанавливаем верхнюю планку прогрессбара
        $callback('total',count($curldata));

        $this->multycurl($curldata,$callback);

        // вписываем полученные данные в базу
        foreach($curldata as $data){
            if($data['code']!='200' || !is_array($data['respond'])) {
                // todo: какая то реакция на грязь в выводе нужна
                $callback('something wrong',$data);
                continue;
            }
            foreach($data['respond'] as $key=>$val){
                $_data = [
                    'endpoint_id' => $key,
                    'type' => $data['api'],
                ];
                $_ep = DB::selectOne('select * from services where `type`=:type and `endpoint_id`=:endpoint_id', $_data);
                $_data['value'] = $val['value'];

                if (!$_ep) {
                    DB::insert('insert into services set value=:value, type=:type, endpoint_id=:endpoint_id,  created_at=now(), updated_at=now()', $_data);
                } else {
                    DB::update('update services set value=:value, updated_at=now() where type=:type and endpoint_id=:endpoint_id', $_data);
                }
            }
        }
    }

}