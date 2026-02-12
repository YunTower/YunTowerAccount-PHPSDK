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

    /** access_token 最大有效期 12 天（秒） */
    private const ACCESS_TOKEN_MAX_EXPIRE = 1036800;
    /** refresh_token 最大有效期 24 天（秒） */
    private const REFRESH_TOKEN_MAX_EXPIRE = 2073600;
    /** 头像文件最大 15MB */
    private const AVATAR_MAX_SIZE = 15 * 1024 * 1024;

    /**
     * 获取用户访问凭证
     * @param string $code 授权码
     * @param int|null $accessTokenExpiresIn 自定义 access_token 有效期（秒），最大 12 天，不传用服务端默认
     * @param int|null $refreshTokenExpiresIn 自定义 refresh_token 有效期（秒），最大 24 天，不传用服务端默认
     * @return array
     */
    public function getUserToken(string $code, ?int $accessTokenExpiresIn = null, ?int $refreshTokenExpiresIn = null): array
    {
        if ($accessTokenExpiresIn !== null && $accessTokenExpiresIn > self::ACCESS_TOKEN_MAX_EXPIRE) {
            throw new AccountException('access_token 有效期不能超过 ' . self::ACCESS_TOKEN_MAX_EXPIRE . ' 秒（12 天）');
        }
        if ($refreshTokenExpiresIn !== null && $refreshTokenExpiresIn > self::REFRESH_TOKEN_MAX_EXPIRE) {
            throw new AccountException('refresh_token 有效期不能超过 ' . self::REFRESH_TOKEN_MAX_EXPIRE . ' 秒（24 天）');
        }
        $data = [
            'code' => $code,
            'appid' => $this->appid,
            'appsecret' => $this->appSecret
        ];
        if ($accessTokenExpiresIn !== null && $accessTokenExpiresIn > 0) {
            $data['access_token_expires_in'] = $accessTokenExpiresIn;
        }
        if ($refreshTokenExpiresIn !== null && $refreshTokenExpiresIn > 0) {
            $data['refresh_token_expires_in'] = $refreshTokenExpiresIn;
        }
        return $this->request->send($this->api_host . '/user/token/get', 'POST', $data);
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
            'appsecret' => $this->appSecret,
            'access_token' => $access_token
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
            'appsecret' => $this->appSecret,
            'refresh_token' => $refresh_token
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
            'appsecret' => $this->appSecret,
            'access_token' => $access_token
        ]);
    }

    /**
     * 获取用户关联账号UID
     * @param string $access_token
     * @return array
     */
    public function getThirdPartyAccount(string $access_token): array
    {
        return $this->request->send($this->api_host . '/user/connect', 'POST', [
            'appid' => $this->appid,
            'appsecret' => $this->appSecret,
            'access_token' => $access_token
        ]);
    }

    /**
     * 设置用户昵称（1-64 字符）
     * @param string $access_token
     * @param string $nickname
     * @return array
     */
    public function setUserNickname(string $access_token, string $nickname): array
    {
        $len = mb_strlen($nickname);
        if ($len < 1 || $len > 64) {
            throw new AccountException('昵称长度须为 1-64 个字符，当前为 ' . $len . ' 个字符');
        }
        return $this->request->send($this->api_host . '/user/nickname', 'POST', [
            'appid' => $this->appid,
            'appsecret' => $this->appSecret,
            'access_token' => $access_token,
            'nickname' => $nickname
        ]);
    }

    /**
     * 设置用户头像（上传本地图片文件）
     * @param string $access_token
     * @param string $avatarPath 本地图片文件路径（jpg/jpeg/png/webp，≤15MB）
     * @return array
     */
    public function setUserAvatar(string $access_token, string $avatarPath): array
    {
        if (!is_file($avatarPath) || !is_readable($avatarPath)) {
            throw new AccountException('头像文件不存在或不可读：' . $avatarPath);
        }
        $size = filesize($avatarPath);
        if ($size > self::AVATAR_MAX_SIZE) {
            throw new AccountException('头像文件不能超过 15MB，当前为 ' . round($size / 1024 / 1024, 2) . 'MB');
        }
        $mime = mime_content_type($avatarPath) ?: 'image/jpeg';
        $data = [
            'appid' => $this->appid,
            'appsecret' => $this->appSecret,
            'access_token' => $access_token,
            'file' => new \CURLFile($avatarPath, $mime, basename($avatarPath))
        ];
        return $this->request->send($this->api_host . '/user/avatar', 'POST', $data);
    }
}