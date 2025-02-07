<?php
/**
 * @author YunTower
 * @desc 云塔公共请求类
 * @date 2025/2/7
 * @version 0.0.1
 */

namespace Yuntower\AccountSDK\Common;

use Yuntower\AccountSDK\Exception\AccountException;

class Request
{
    private false|\CurlHandle $curl;

    /**
     * 发送请求
     * @param string $url
     * @param string $method 请求方式 GET|POST
     * @param array $data 请求数据
     * @param array $header Header
     * @param array $cookie Cookie
     * @return array
     */
    public function send(string $url, string $method, array $data = [], array $header = [], array $cookie = []): array
    {
        try {
            $_method = ['GET', 'POST'];
            $this->curl = curl_init();

            if (!in_array($method, $_method)) {
                throw new AccountException('不被允许的请求方式');
            }

            if ($method == 'POST') {
                curl_setopt($this->curl, CURLOPT_POST, 1);
            }

            // 处理header
            if (!empty($header)) {
                $_headers = [];
                foreach ($header as $key => $value) {
                    $_headers[] = $key . ': ' . $value;
                }
                curl_setopt($this->curl, CURLOPT_HTTPHEADER, $_headers);
            }

            // 处理cookie
            if (!empty($_cookie)) {
                $_cookie = '';
                foreach ($cookie as $key => $value) {
                    $_cookie .= $key . '=' . $value . '; ';
                }
                curl_setopt($this->curl, CURLOPT_COOKIE, $_cookie);
            }

            curl_setopt($this->curl, CURLOPT_URL, $url);
            if (!empty($data)) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data));
            }
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curl, CURLOPT_TIMEOUT, 30); // 设置最大执行时间为30秒
            curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 10); // 设置连接等待时间为10秒
            $response = curl_exec($this->curl);
            if (!$response) {
                return ['code' => 500, 'msg' => '无法连接到目标服务器', 'error' => curl_error($this->curl)];
            } else {
                return json_decode($response, true);
            }
        } catch (AccountException $e) {
            return ['code' => 500, 'msg' => $e->getMessage()];
        }
    }
}