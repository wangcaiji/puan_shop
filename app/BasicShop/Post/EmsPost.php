<?php


namespace App\BasicShop\Post;


use App\Models\Address;
use App\Models\Order;
use Curl\Curl;
use Overtrue\Wechat\Utils\XML;

/**
 * Class EmsPost
 * @package App\Werashop\Post
 */
class EmsPost implements PostInterface
{
    /**
     * @var string
     */
    private $_getBillnumUrl;

    /**
     * @var string
     */
    private $_printDatasUrl;

    /**
     * @var mixed
     */
    private $_sysAccount;

    /**
     * @var mixed
     */
    private $_password;

    /**
     * @var mixed
     */
    private $_appKey;

    /**
     * EmsPost constructor.
     */
    public function __construct()
    {
        $this->_getBillnumUrl = "http://os.ems.com.cn:8081/zkweb/bigaccount/getBigAccountDataAction.do";
        $this->_printDatasUrl = "http://os.ems.com.cn:8081/zkweb/bigaccount/getBigAccountDataAction.do?method=updatePrintDatas";

        $this->_sysAccount = env('EMS_SYS_ACCOUNT');
        $this->_password = env('EMS_PASSWORD');
        $this->_appKey = env('EMS_APPKEY');
    }


    /**
     * @return string
     */
    public function getMailNo()
    {
        $curl = new Curl();

        $curl->get($this->_getBillnumUrl, [
            'method' => 'getBillNumBySys',
            'xml' => $this->generateBillNumRequestData()
        ]);
        $xml_str = $curl->response;

        $xml = simplexml_load_string(base64_decode($xml_str));
        return (string) $xml->assignIds->assignId->billno;
    }

    /**
     * @return string
     */
    protected function generateBillNumRequestData()
    {
        $str = '<?xml version="1.0" encoding="UTF-8"?><XMLInfo><sysAccount>' . $this->_sysAccount . '</sysAccount><passWord>' . $this->_password . '</passWord><appKey>' . $this->_appKey . '</appKey><businessType>4</businessType><billNoAmount>1</billNoAmount></XMLInfo>';

        return base64_encode($str);
    }

    protected function generateUpdatePrintDatasRequestData()
    {
//        $xml = XML::build([
//            'sysAccount' => $this->_sysAccount,
//            'passWord' => $this->_password,
//            'appKey' => $this->_appKey,
//            'printKind' => ,
//            'printDatas' => [
//                'printData' =>
//            ]
//        ], 'XMLInfo');
    }
}