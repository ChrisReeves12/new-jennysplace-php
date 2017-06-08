<?php
/**
* The Order class definition.
*
* This class represents orders that have been placed.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop\ShopList;

use Doctrine\ORM\Mapping\ManyToOne;
use Library\Model\Product\Status;
use Library\Model\Shop\PaymentMethod;
use Library\Model\Shop\ShopList;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @Entity
 * @Table(name="orders")
 * @HasLifecycleCallbacks
**/
class Order extends ShopList
{
    /**
     * @Column(name="trans_id", type="string", length=500, nullable=true)
     * @var string
     */
    protected $transaction_id;

    /**
     * @Column(name="auth_code", type="string", length=500, nullable=true)
     * @var string
     */
    protected $auth_code;

    /**
     * @Column(name="order_num", type="integer", nullable=false, unique=true)
     * @var int
     */
    protected $order_number;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\PaymentMethod")
     * @JoinColumn(name="payment_method_id", referencedColumnName="id")
     * @var PaymentMethod
     */
    protected $payment_method;

    /**
     * @Column(name="status", type="string", length=500, nullable=false)
     * @var Status
     */
    protected $status;

    /**
     * @Column(name="shipping_date", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $shipping_date;

    /**
     * @Column(name="tracking_number", type="string", length=500, nullable=true)
     * @var string
     */
    protected $tracking_number;

    /**
     * @Column(name="original_grand_total", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $original_grand_total;

    /**
     * @return float
     */
    public function getOriginalGrandTotal()
    {
        return $this->original_grand_total;
    }

    /**
     * @param float $original_grand_total
     */
    public function setOriginalGrandTotal($original_grand_total)
    {
        $this->original_grand_total = $original_grand_total;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->tracking_number;
    }

    /**
     * @param string $tracking_number
     */
    public function setTrackingNumber($tracking_number)
    {
        $this->tracking_number = $tracking_number;
    }

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @param PaymentMethod $payment_method
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @return \DateTime
     */
    public function getShippingDate()
    {
        return $this->shipping_date;
    }

    /**
     * @param \DateTime $shipping_date
     */
    public function setShippingDate($shipping_date)
    {
        $this->shipping_date = $shipping_date;
    }

    /**
     * @param string $transaction_id
     */
    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->auth_code;
    }

    /**
     * @param string $auth_code
     */
    public function setAuthCode($auth_code)
    {
        $this->auth_code = $auth_code;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * @param int $order_number
     */
    public function setOrderNumber($order_number)
    {
        $this->order_number = $order_number;
    }

    /**
     * Calculate total of order
     *
     * @param ServiceLocatorInterface $service_manager
     */
    public function calculateTotals($service_manager)
    {
        parent::calculateTotals($service_manager);

        // Save original total if it hasn't been set
        if (empty($this->getOriginalGrandTotal()))
            $this->setOriginalGrandTotal($this->getTotal());
    }
}