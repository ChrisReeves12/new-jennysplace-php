<?php
/**
 * The PaymentService class definition.
 *
 * Handles loading and processing payments
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/
namespace Library\Service;

use Library\Model\Shop\IPayMethodStrategy;
use Library\Model\Shop\PaymentMethod;

/**
 * Class PaymentService
 * @package Library\Service
 */
class PaymentService extends AbstractService
{
    /**
     * Processes a payment
     *
     * @param PaymentMethod $paymentMethod
     * @param array $info
     *
     * @return array
     * @throws \Exception
     */
    public function process_payment(PaymentMethod $paymentMethod, $info = [])
    {
        // Get instance of plugin
        $payment_strategy_class = $paymentMethod->getProcessStrategy();
        $pay_strategy = new $payment_strategy_class();
        $pay_strategy->setServiceManager($this->getServiceManager());
        if (!($pay_strategy instanceof IPayMethodStrategy))
        {
            throw new \Exception("The payment plugin specified for this payment method is invalid.");
        }
        $result = $pay_strategy->process($info);
        return $result;
    }
}