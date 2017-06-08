<?php
/**
* The HomeController class definition.
*
* The main backend page controller
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Service\Report\ReportService;

class HomeController extends JPController
{
    public function indexAction()
    {
        // Get sales revenue
        $report_service = $this->getServiceLocator()->get('report');
        $grand_totals = $report_service->generateTotalSalesRevenue();
        $year_grand_totals = $report_service->generateYearTotalSalesRevenue();
        $month_grand_totals = $report_service->generateMonthTotalSalesRevenue();
        $today_grand_totals = $report_service->generateTodayTotalSalesRevenue();

        // Get sales count
        $total_order_amount = $report_service->generateTotalOrderCount();
        $total_year_order_amount = $report_service->generateYearTotalOrderCount();
        $total_today_order_amount = $report_service->generateTodayOrderCount();

        // Get customers that are new
        $new_customers = $report_service->generateNewCustomerReport();
        $recent_orders = $report_service->generateRecentOrders();

        return [
            'grand_totals' => $grand_totals,
            'year_grand_totals' => $year_grand_totals,
            'month_grand_totals' => $month_grand_totals,
            'today_grand_totals' => $today_grand_totals,
            'total_order_amount' => $total_order_amount,
            'total_year_order_amount' => $total_year_order_amount,
            'total_today_order_amount' => $total_today_order_amount,
            'new_customers' => $new_customers,
            'recent_orders' => $recent_orders
        ];
    }
}