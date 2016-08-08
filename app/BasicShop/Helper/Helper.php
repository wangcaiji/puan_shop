<?php

namespace App\BasicShop\Helper;

use App\Constants\AppConstant;
use App\Models\Customer;
use App\BasicShop\Exceptions\UserNotCachedException;
use App\BasicShop\Exceptions\UserNotSubscribedException;
use Curl\Curl;

/**
 * Class Helper
 * @package App\BasicShop\Helper
 */
class Helper
{
    /**
     * @return mixed
     * @throws UserNotCachedException
     * @throws UserNotSubscribedException;
     */
    public function getSessionCachedUser()
    {
        if (!$this->hasSessionCachedUser()) {
            throw new UserNotCachedException;
        }
        $user = \Session::get(AppConstant::SESSION_USER_KEY);

        if (is_null($user)) {
            throw new UserNotSubscribedException;
        }
        return $user;
    }

    /**
     * @return bool
     */
    public function hasSessionCachedUser()
    {
        return \Session::has(AppConstant::SESSION_USER_KEY);
    }

    /**
     * @return array
     */
    public function getUser()
    {
        try {
            $user = \Helper::getSessionCachedUser();

            return $user;
        } catch (\Exception $e) {
            abort('404');
        }
    }

    /**
     * @return \App\Models\Customer;
     */
    public function getCustomer()
    {
        try {
            $user = self::getSessionCachedUser();
            $customer = Customer::where('openid', $user['openid'])->firstOrFail();

            return $customer;
        } catch (\Exception $e) {
            abort('404');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|static
     * @throws UserNotCachedException
     * @throws UserNotSubscribedException
     */
    public function getCustomerOrFail()
    {
        $user = self::getSessionCachedUser();
        $customer = Customer::where('openid', $user['openid'])->firstOrFail();

        return $customer;
    }

    /**
     * @return \App\Models\Customer|null|static
     */
    public function getCustomerOrNull()
    {
        try {
            $user = self::getSessionCachedUser();
            $customer = Customer::where('openid', $user['openid'])->firstOrFail();

            return $customer;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $file
     * @return mixed
     */
    public function qiniuUpload($file)
    {
        $clientName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $newName = md5(date('ymdhis') . $clientName) . "." . $extension;
        $disk = \Storage::disk('qiniu');
        $contents = file_get_contents($file->getRealPath());
        $disk->put($newName, $contents);
        return $disk->getDriver()->downloadUrl($newName);
    }


    /**
     * @param $unionid
     * @return bool
     */
    public function getBeansByUnionid($unionid)
    {
        $curl = new Curl();
        $curl->post('http://www.ohmate.cn/puan/beans-for-union-id', array(
            'unionid' => $unionid
        ));

        if ($curl->error) {
            $curl->close();
            return false;
        } else {
            $data = json_decode($curl->response);
            if ($data->success) {
                return $data->data->beans;
            }
            return false;
        }
    }

    /**
     * @param $unionid
     * @param $beans
     * @return bool
     */
    public function updateBeansByUnionid($unionid, $beans)
    {
        //http://www.ohmate.cn/puan/update-beans-when-purchase-for-union-id?unionid=123123&beans=1
        $curl = new Curl();
        $curl->post('http://www.ohmate.cn/puan/update-beans-when-purchase-for-union-id', array(
            'unionid' => $unionid,
            'beans' => $beans,
        ));

        if ($curl->error) {
            $curl->close();
            return false;
        } else {
            $data = json_decode($curl->response);
            return $data->success;
        }
    }

}