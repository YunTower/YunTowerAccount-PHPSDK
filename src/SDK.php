<?php
/**
 * @author YunTower
 * @desc 云塔账号通行证SDK
 * @date 2024/2/7
 * @version 0.0.1
 */

namespace Yuntower\AccountSDK;

use Yuntower\AccountSDK\Common\Request;
use Yuntower\AccountSDK\Exception\AccountException;

class SDK
{
    private string $appid;
    private string $appSecret;
    private Request $request;
    private string $api_host = 'https://v1.api.account.yuntower.com';

    public function __construct(string $appid, string $appSecret)
    {
        if (!$appid || !$appSecret) {
            throw new AccountException('appid或appSecret不能为空');
        }
        $this->appid = $appid;
        $this->appSecret = $appSecret;
        $this->request = new Request();
    }

    /**
     * 获取用户访问凭证
     * @param string $token
     * @param string $tuid
     * @return array
     */
    public function getUserToken(string $token, string $tuid): array
    {
        return $this->request->send($this->api_host . '/user/token/get', 'POST', [
            'token' => $token,
            'tuid' => $tuid,
            'appid' => $this->appid,
            'appsecret' => $this->appSecret
        ]);
    }

    /**
     * 获取用户信息
     * @param string $access_token
     * @return array
     */
    public function getUserInfo(string $access_token): array
    {
        return $this->request->send($this->api_host . '/user/data', 'POST', [
            'appid' => $this->appid,
            'appsecret' => $this->appSecret
        ], [
            'Authorization' => 'Bearer ' . $access_token
        ]);
    }

    /**
     * 刷新用户访问凭证
     * @param string $refresh_token
     * @return array
     */
    public function refreshUserToken(string $refresh_token): array
    {
        return $this->request->send($this->api_host . '/user/token/refresh', 'POST', [
            'appid' => $this->appid,
            'appsecret' => $this->appSecret
        ], [
            'Authorization' => 'Bearer ' . $refresh_token
        ]);
    }

    /**
     * 退出登录状态
     * @param string $access_token
     * @return array
     */
    public function logout(string $access_token): array
    {
        return $this->request->send($this->api_host . '/user/logout', 'POST', [
            'appid' => $this->appid,
            'appsecret' => $this->appSecret
        ], [
            'Authorization' => 'Bearer ' . $access_token
        ]);
    }
}