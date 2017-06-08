<?php
/**
* The General class definition.
*
* This is the general store settings form in the backend
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Form\Settings;

use Library\Form\MSForm;
use Library\Service\DB\EntityManagerSingleton;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;

/**
 * Class General
 * @package Library\Form\Settings
 */
class General extends MSForm
{
    protected $inputFilter;

    public function __construct($name = null)
    {
        parent::__construct('general_settings');

        // Get US states
        $contents = file_get_contents(getcwd() . '/data/json/States.json');
        $json = json_decode($contents);

        foreach ($json as $state)
        {
            $states_options[$state->abbreviation] = $state->name;
        }

        // Get Themes
        $working_dir = getcwd();
        chdir($working_dir . '/public/themes');
        $dirs = array_filter(glob('*'), 'is_dir');
        $themes = [];

        // Check the validity of each theme and see if there is a config file
        foreach ($dirs as $dir)
        {
            if (file_exists($dir . '/config.json'))
            {
                $contents = json_decode(file_get_contents($dir . '/config.json'), true);
                if (!empty($contents['theme_name']))
                {
                    $themes[$dir] = $contents['theme_name'];
                }
            }
        }
        chdir($working_dir);

        // Get categories
        $em = EntityManagerSingleton::getInstance();
        $category_options = [0 => 'None'];
        $category_listings = $em->getRepository('Library\Model\Category\Category')->findAllWithHierarchy();

        if (!empty($category_listings))
        {
            foreach($category_listings as $listing)
            {
                // Construct listing name
                $listing_name = "";
                if (count($listing['ancestors']) > 0)
                {
                    foreach ($listing['ancestors'] as $ancestor)
                    {
                        $listing_name .= $ancestor['name'] . " >> ";
                    }
                }

                $listing_name .= $listing['name'];
                $category_options[$listing['id']] = $listing_name;
            }
        }

        // Get discounts
        $discount_options = [0 => 'None'];
        $discounts = $em->getRepository('Library\Model\Shop\Discount')->findAll();
        if (count($discounts) > 0)
        {
            foreach ($discounts as $discount)
            {
                $discount_options[$discount->getId()] = $discount->getName();
            }
        }

        $this->add([
            'name'    => 'site_title',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Website Title'
            ]
        ]);

        $this->add([
            'name'    => 'store_name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Name'
            ]
        ]);

        $this->add([
            'name'    => 'session_name',
            'type'    => 'Text',
            'options' => [
                'label' => 'Session Name'
            ]
        ]);

        $this->add([
            'name'       => 'store_logo',
            'type'       => 'File',
            'options'    => [
                'label' => 'Store Logo'
            ]
        ]);

        $this->add([
            'name'       => 'image_path',
            'type'       => 'Text',
            'options'    => [
                'label' => 'Image Path'
            ]
        ]);

        $this->add([
            'name'    => 'frontend_theme',
            'type'    => 'Select',
            'options' => [
                'label' => 'Frontend Theme'
            ],
            'attributes' => [
                'options' => $themes
            ]
        ]);

        $this->add([
            'name'    => 'site_email',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Email'
            ]
        ]);

        $this->add([
            'name'    => 'show_prices',
            'type'    => 'Select',
            'options' => [
                'label' => 'Show Prices?'
            ],
            'attributes' => [
                'options' => [
                    1 => 'Show Prices To Everyone',
                    0 => 'Hide Prices From Guests'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'site_url',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store URL'
            ]
        ]);

        $this->add([
            'name' => 'chart_shipping_calculation_method',
            'type' => 'Select',
            'options' => [
                'label' => 'Chart Shipping Calculation Method'
            ],
            'attributes' => [
                'options' => [
                    'weight' => 'Order Total Weight',
                    'sub-total' => 'Order Sub-Total'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'store_address_line_1',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Address Line 1'
            ]
        ]);

        $this->add([
            'name'    => 'store_address_line_2',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Address Line 2'
            ]
        ]);

        $this->add([
            'name'    => 'store_address_city',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Address City'
            ]
        ]);

        $this->add([
            'name'    => 'store_address_state',
            'type'    => 'Select',
            'options' => [
                'label' => 'Store Address State'
            ],
            'attributes' => [
                'options' => $states_options
            ]
        ]);

        $this->add([
            'name'    => 'store_address_zipcode',
            'type'    => 'Text',
            'options' => [
                'label' => 'Store Address Zipcode'
            ]
        ]);

        $this->add([
            'name' => 'inventory_check',
            'type' => 'Select',
            'options' => [
                'label' => 'Auto Inventory Update'
            ],
            'attributes' => [
                'options' => [
                    0 => 'Off',
                    1 => 'On',
                ]
            ]
        ]);

        $this->add([
            'name'    => 'global_discount',
            'type'    => 'Select',
            'options' => [
                'label' => 'Sitewide Discount'
            ],
            'attributes' => [
                'options' => $discount_options
            ]
        ]);

        $this->add([
            'name'    => 'multi_discount',
            'type'    => 'Select',
            'options' => [
                'label' => 'Allow Multiple Discounts?'
            ],
            'attributes' => [
                'options' => [
                    0 => 'No',
                    1 => 'Yes'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'home_page_category',
            'type'    => 'Select',
            'options' => [
                'label' => 'Home Page Category'
            ],
            'attributes' => [
                'options' => $category_options
            ]
        ]);

        $this->add([
            'name'       => 'require_cust_validate',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Require Customer Email Validation'
            ],
            'attributes' => [
                'options' => [
                    1 => 'Require Email Validation',
                    0 => 'Do Not Require Email Validation'
                ]
            ]
        ]);

        $this->add([
            'name'       => 'products_per_page',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Products Per Page'
            ],
            'attributes' => [
                'options' => [
                    8  => '8',
                    16 => '16',
                    24 => '24',
                    32 => '32',
                    40 => '40',
                    48 => '48',
                    56 => '56',
                    64 => '64',
                    72 => '72',
                    80 => '80',
                    88 => '88',
                    96 => '96'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'minimum_product_total',
            'type'    => 'Text',
            'options' => [
                'label' => 'Minimum Product Total'
            ]
        ]);

        $this->add([
            'name' => 'shipping_methods',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'options' => [
                'label' => 'Shipping Methods'
            ],
            'attributes' => [
                'options' => [
                    'Library\\Plugin\\ShippingMethod\\Ups' => 'UPS',
                    'Library\\Plugin\\ShippingMethod\\Fedex' => 'Fedex',
                    'Library\\Plugin\\ShippingMethod\\Usps' => 'Usps',
                    'Library\\Plugin\\ShippingMethod\\Chart' => 'Chart'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'ups_access_key',
            'type'    => 'Text',
            'options' => [
                'label' => 'UPS Access Key'
            ]
        ]);

        $this->add([
            'name'    => 'usps_user_id',
            'type'    => 'Text',
            'options' => [
                'label' => 'USPS API User ID'
            ]
        ]);

        $this->add([
            'name'    => 'ups_login_name',
            'type'    => 'Text',
            'options' => [
                'label' => 'UPS Login Name'
            ]
        ]);

        $this->add([
            'name'    => 'ups_password',
            'type'    => 'Text',
            'options' => [
                'label' => 'UPS Password'
            ]
        ]);

        $this->add([
            'name'    => 'fedex_key',
            'type'    => 'Text',
            'options' => [
                'label' => 'Fedex Access Key'
            ]
        ]);

        $this->add([
            'name'    => 'fedex_password',
            'type'    => 'Text',
            'options' => [
                'label' => 'Fedex Access Password'
            ]
        ]);

        $this->add([
            'name'    => 'fedex_account_number',
            'type'    => 'Text',
            'options' => [
                'label' => 'Fedex Account Number'
            ]
        ]);

        $this->add([
            'name'    => 'fedex_meter_number',
            'type'    => 'Text',
            'options' => [
                'label' => 'Fedex Meter Number'
            ]
        ]);

        $this->add([
            'name'    => 'authorize_net_login',
            'type'    => 'Text',
            'options' => [
                'label' => 'Authorize.net Login'
            ]
        ]);

        $this->add([
            'name'    => 'authorize_net_tran_key',
            'type'    => 'Text',
            'options' => [
                'label' => 'Authorize.net Transaction Key'
            ]
        ]);

        $this->add([
            'name'       => 'authorize_net_sandbox',
            'type'       => 'Select',
            'options'    => [
                'label' => 'Authorize.net Sandbox Mode?'
            ],
            'attributes' => [
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ]
            ]
        ]);

        $this->add([
            'name'    => 'paypal_client_id',
            'type'    => 'Text',
            'options' => [
                'label' => 'PayPal Client ID'
            ]
        ]);

        $this->add([
            'name'    => 'paypal_client_secret',
            'type'    => 'Text',
            'options' => [
                'label' => 'PayPal Client Secret'
            ]
        ]);

        $this->add([
            'name'    => 'paypal_cart_description',
            'type'    => 'Text',
            'options' => [
                'label' => 'PayPal Cart Description'
            ]
        ]);

        $this->add([
            'name'       => 'paypal_sandbox_mode',
            'type'       => 'Select',
            'options'    => [
                'label' => 'PayPal Sandbox Mode?'
            ],
            'attributes' => [
                'options' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ]
            ]
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'Submit',
            'attributes' => [
                'value' => 'Save Changes'
            ]
        ]);
    }

    public function getInputFilter()
    {

        if (!$this->inputFilter)
        {

            $inputFilter = new InputFilter();
            $fileInput = new FileInput('store_logo');
            $fileInput->setRequired(false);

            // Add validators
            $fileInput->getValidatorChain()
                ->attachByName('filesize', ['max' => 2097152])
                ->attachByName('fileimagesize', ['minWidth' => 64, 'minHeight' => 64])
                ->attachByName('Zend\Validator\File\Extension', ['extension' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'dib']]);

            // Add filters
            $fileInput->getFilterChain()->attachByName('filerenameupload', [
                'target'               => getcwd() . '/public/img/layout_images/',
                'randomize'            => true,
                'use_upload_extension' => true
            ]);

            // Add file filter
            $inputFilter->add($fileInput);

            $inputFilter->add([
                'name'     => 'site_title',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'site_email',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'image_path',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'session_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'show_prices',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'shipping_methods',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'frontend_theme',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'inventory_check',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'minimum_product_total',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'store_name',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'require_cust_validate',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'home_page_category',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'products_per_page',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'site_url',
                'required' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'global_discount',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'multi_discount',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'usps_user_id',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'ups_access_key',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'ups_login_name',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'ups_password',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'fedex_key',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'fedex_password',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'fedex_account_number',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'fedex_meter_number',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'authorize_net_login',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'authorize_net_tran_key',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'authorize_net_sandbox',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'paypal_client_id',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'paypal_client_secret',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'paypal_sandbox_mode',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name'     => 'paypal_sandbox_mode',
                'required' => false,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'store_address_line_1',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'store_address_line_2',
                'required' => false,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'store_address_city',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'store_address_state',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $inputFilter->add([
                'name' => 'store_address_zipcode',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ]
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
