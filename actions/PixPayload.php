<?php
// actions/PixPayload.php

class PixPayload {
    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_POINT_OF_INITIATION_METHOD = '01'; // 11 (Dynamic) or 12 (Static) ? 12 for static usually, but 01 usually works for simple static
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    private $pixKey;
    private $description;
    private $merchantName;
    private $merchantCity;
    private $txid;
    private $amount;

    public function setPixKey($pixKey) {
        $this->pixKey = $pixKey;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setMerchantName($merchantName) {
        $this->merchantName = $merchantName;
        return $this;
    }

    public function setMerchantCity($merchantCity) {
        $this->merchantCity = $merchantCity;
        return $this;
    }

    public function setTxid($txid) {
        $this->txid = $txid;
        return $this;
    }

    public function setAmount($amount) {
        $this->amount = number_format((float)$amount, 2, '.', '');
        return $this;
    }

    private function getValue($id, $value) {
        $size = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $size . $value;
    }

    private function getMerchantAccountInformation() {
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');
        $key = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->pixKey);
        $desc = !empty($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->description) : '';

        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui . $key . $desc);
    }

    private function getAdditionalDataFieldTemplate() {
        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txid ? $this->txid : '***');
        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    private function normalize($string, $limit) {
        $string = preg_replace('/[^a-zA-Z0-9 ]/', '', strtoupper($this->removeAccents($string)));
        return substr($string, 0, $limit);
    }

    private function removeAccents($string) {
        return strtr(utf8_decode($string), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public function getPayload() {
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR, '01');
        $payload .= $this->getValue(self::ID_POINT_OF_INITIATION_METHOD, '12');
        $payload .= $this->getMerchantAccountInformation();
        $payload .= $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, '0000');
        $payload .= $this->getValue(self::ID_TRANSACTION_CURRENCY, '986');
        $payload .= $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->amount);
        $payload .= $this->getValue(self::ID_COUNTRY_CODE, 'BR');
        $payload .= $this->getValue(self::ID_MERCHANT_NAME, $this->normalize($this->merchantName, 25));
        $payload .= $this->getValue(self::ID_MERCHANT_CITY, $this->normalize($this->merchantCity, 15));
        $payload .= $this->getAdditionalDataFieldTemplate();

        return $payload . $this->getCRC16($payload);
    }

    private function getCRC16($payload) {
        $payload .= self::ID_CRC16 . '04';

        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        return self::ID_CRC16 . '04' . strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }
}
?>
