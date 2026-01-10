<?php
interface PaymentGatewayInterface {
    public function createPayment($amount, $type = 'credit_card');
    public function checkStatus($paymentId);
    public function cancelPayment($paymentId);
}
?>
