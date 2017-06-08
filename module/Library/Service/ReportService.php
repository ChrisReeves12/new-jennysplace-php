<?php
/**
* The ReportService class definition.
*
* This service generates reports on sales and customers
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Shop\ShopList\Order;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ReportService
 * @package Library\Service\Report
 */
class ReportService extends AbstractService
{
    /**
     * Returns the total revenue for all sales
     *
     * @param bool $include_pending_orders
     * @return float
     */
    public function generateTotalSalesRevenue($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $total = 0.00;

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']));

            $orders = $qb->getQuery()->getArrayResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped']));

            $orders = $qb->getQuery()->getArrayResult();
        }

        // Add totals up
        foreach ($orders as $order)
        {
            $total += $order['total'];
        }

        return $total;
    }

    /**
     * Returns the total revenue for all sales in the year
     *
     * @param bool $include_pending_orders
     * @return float
     */
    public function generateYearTotalSalesRevenue($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $total = 0.00;

        // Create dates to calculate range
        $first_date = new \DateTime();
        $last_date = new \DateTime();
        $first_date_string = $first_date->format('Y') . '-01-01';
        $last_date_string = $last_date->format('Y-m-d h:i:s');

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $orders = $qb->getQuery()->getArrayResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $orders = $qb->getQuery()->getArrayResult();
        }

        // Add totals up
        foreach ($orders as $order)
        {
            $total += $order['total'];
        }

        return $total;
    }

    /**
     * Returns the total revenue of orders in the current month
     *
     * @param bool $include_pending_orders
     * @return float $total
     */
    public function generateMonthTotalSalesRevenue($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $total = 0.00;

        // Create dates to calculate range
        $first_date = new \DateTime();
        $last_date = new \DateTime();
        $first_date_string = $first_date->format('Y') . '-' . $first_date->format('m') . '-01';
        $last_date_string = $last_date->format('Y-m-d h:i:s');

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $orders = $qb->getQuery()->getArrayResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $orders = $qb->getQuery()->getArrayResult();
        }

        // Add totals up
        foreach ($orders as $order)
        {
            $total += $order['total'];
        }

        return $total;
    }

    /**
     * Generate total sales revenue for the current day
     *
     * @param bool $include_pending_orders
     * @return float
     */
    public function generateTodayTotalSalesRevenue($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $total = 0.00;

        // Create dates to calculate range
        $first_date = new \DateTime();
        $last_date = new \DateTime();
        $first_date_string = $first_date->format('Y-m-d');
        $last_date_string = $last_date->format('Y-m-d h:i:s');

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $orders = $qb->getQuery()->getArrayResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select('partial o.{id,total}')
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $orders = $qb->getQuery()->getArrayResult();
        }

        // Add totals up
        foreach ($orders as $order)
        {
            $total += $order['total'];
        }

        return $total;
    }

    /**
     * Returns the total amount of orders
     * @param bool $include_pending_orders
     *
     * @return int
     */
    public function generateTotalOrderCount($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select($qb->expr()->count('o'))
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']));

            $total = $qb->getQuery()->getSingleScalarResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select($qb->expr()->count('o'))
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']));

            $total = $qb->getQuery()->getSingleScalarResult();
        }

        return $total;
    }

    /**
     * Get total sales for the current year
     * @param bool $include_pending_orders
     *
     * @return int
     */
    public function generateYearTotalOrderCount($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Create dates to calculate range
        $first_date = new \DateTime();
        $last_date = new \DateTime();
        $first_date_string = $first_date->format('Y') . '-01-01';
        $last_date_string = $last_date->format('Y-m-d h:i:s');

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select($qb->expr()->count('o'))
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $total = $qb->getQuery()->getSingleScalarResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select($qb->expr()->count('o'))
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $total = $qb->getQuery()->getSingleScalarResult();
        }

        return $total;
    }

    /**
     * Generate total order count for the current day
     *
     * @param bool $include_pending_orders
     * @return int
     */
    public function generateTodayOrderCount($include_pending_orders = false)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Create dates to calculate range
        $first_date = new \DateTime();
        $last_date = new \DateTime();
        $first_date_string = $first_date->format('Y-m-d');
        $last_date_string = $last_date->format('Y-m-d h:i:s');

        if (!$include_pending_orders)
        {
            $qb = $em->createQueryBuilder();
            $qb->select($qb->expr()->count('o'))
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $total = $qb->getQuery()->getSingleScalarResult();
        }
        else
        {
            $qb = $em->createQueryBuilder();
            $qb->select($qb->expr()->count('o'))
                ->from('Library\Model\Shop\ShopList\Order', 'o')
                ->where($qb->expr()->in('o.status', ['Completed', 'Shipped', 'Pending']))
                ->andWhere($qb->expr()->between('o.date_created', ':first_date', ':last_date'))
                ->setParameters(['first_date' => $first_date_string, 'last_date' => $last_date_string]);

            $total = $qb->getQuery()->getSingleScalarResult();
        }

        return $total;
    }

    /**
     * Returns the recent customers that have joined
     *
     * @param int $limit
     * @return array
     */
    public function generateNewCustomerReport($limit = 15)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select('partial u.{id,first_name,last_name,date_created}')
            ->from('Library\Model\User\User', 'u')
            ->orderBy('u.date_created', 'DESC')
            ->setMaxResults($limit);

        $users = $qb->getQuery()->getArrayResult();

        // Generate table report
        $user_report = [];
        foreach ($users as $user)
        {
            $user_report[$user['id']] = [
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'date' => $user['date_created']
            ];
        }

        return $user_report;
    }

    /**
     * Get recent orders
     *
     * @param int $limit
     * @return Order[]
     */
    public function generateRecentOrders($limit = 20)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select('o')
            ->from('Library\Model\Shop\ShopList\Order', 'o')
            ->orderBy('o.date_created', 'DESC')
            ->setMaxResults($limit);

        $orders = $qb->getQuery()->getResult();
        return $orders;
    }
}