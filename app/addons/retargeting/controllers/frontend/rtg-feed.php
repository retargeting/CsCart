<?php

/**
 * Require composer autoload
 */
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

/**
 * Class RTGFeeds
 */
class RTGFeeds
{
    /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var int
     */
    protected $lastPage = 1;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @var int
     */
    protected $totalRows = 0;

    /**
     * @var null
     */
    protected $token = null;

    /**
     * @var \RetargetingSDK\CustomersFeed|\RetargetingSDK\ProductFeed
     */
    protected $feed;

    /**
     * RTGFeeds constructor.
     */
    public function __construct()
    {
        $this->validateReqParams();
    }

    /**
     * Get customers feed
     *
     * @throws Exception
     */
    public function getCustomers()
    {
        if(!empty($this->token))
        {
            list($customers, $params) = fn_get_users([ 'page' => $this->currentPage ], $_SESSION['auth'], $this->perPage);

            $this->feed = new \RetargetingSDK\CustomersFeed($this->token);

            foreach ($customers AS $customer)
            {
                $RTGCustomer = new \RetargetingSDK\Customer();
                $RTGCustomer->setFirstName($customer['firstname']);
                $RTGCustomer->setLastName($customer['lastname']);
                $RTGCustomer->setEmail($customer['email']);
                $RTGCustomer->setStatus($customer['status'] == 'A');
                $RTGCustomer->setPhone($customer['phone']);

                $this->feed->addCustomer($RTGCustomer->getData(true));
            }

            $this->output('customers', $params['total_items']);
        }
        else
        {
            echo 'Token arg is missing or is empty!';
        }
    }

    /**
     * Get products feed
     *
     * @throws Exception
     */
    public function getProducts()
    {
        list($products, $params) = fn_get_products([ 'page' => $this->currentPage ], $this->perPage);

        fn_gather_additional_products_data($products, [
            'get_icon'      => true,
            'get_detailed'  => true,
            'get_discounts' => false
        ]);

        $this->feed = new \RetargetingSDK\ProductFeed();

        foreach ($products AS $product)
        {
            $RTGProduct = new \RetargetingSDK\Product();
            $RTGProduct->setId($product['product_id']);
            $RTGProduct->setName($product['product']);
            $RTGProduct->setUrl(fn_url('products.view?product_id=' . $product['product_id']));

            // Price
            $regularPrice  = (float)$product['list_price'];
            $salePrice     = (float)$product['price'];

            if ($regularPrice <= 0)
            {
                $regularPrice = $salePrice;
            }
            else
            {
                $RTGProduct->setPromo($salePrice);
            }

            $RTGProduct->setPrice($regularPrice);

            // Images
            if (!empty($product['main_pair']['detailed']['image_path']))
            {
                $RTGProduct->setImg($product['main_pair']['detailed']['image_path']);
            }

            $additionalImages = [];

            if (!empty($product['product_options']))
            {
                foreach ($product['product_options'] AS $productOption)
                {
                    if (!empty($productOption['variants']))
                    {
                        foreach ($productOption['variants'] AS $variant)
                        {
                            if (!empty($variant['image_pair']['icon']['image_path']))
                            {
                                $additionalImages[] = $variant['image_pair']['icon']['image_path'];
                            }
                        }
                    }
                }
            }

            if (!empty($additionalImages))
            {
                $RTGProduct->setAdditionalImages($additionalImages);
            }

            // Category
            if (!empty($product['main_category']))
            {
                $categories = fn_get_categories_list_with_parents([ $product['main_category'] ]);

                if (!empty($categories[$product['main_category']]))
                {
                    $category = $categories[$product['main_category']];

                    $RTGCategory = new \RetargetingSDK\Category();
                    $RTGCategory->setId($category['category_id']);
                    $RTGCategory->setName($category['category']);

                    if (!empty($category['parent_id']))
                    {
                        $RTGCategory->setParent($category['parent_id']);

                        if (!empty($category['parents']))
                        {
                            $RTGCategory->setBreadcrumb(
                                $this->getCategoryBreadcrumbs($category['parents'])
                            );
                        }
                    }

                    $RTGProduct->setCategory([ $RTGCategory->getData(false) ]);
                }
            }

            // Inventory
            $RTGProduct->setInventory([
                'variations' => false,
                'stock'      => $product['amount'] > 0
            ]);

            $this->feed->addProduct($RTGProduct->getData(false));
        }

        $this->output('products', $params['total_items']);
    }

    /**
     * Output results
     *
     * @param $type
     * @param $totalItems
     */
    protected function output($type, $totalItems)
    {
        // Feed URL
        $feedURL = fn_url('rtg-feed.' . $type . '&per_page=' . $this->perPage, 'C');

        // Last page
        $this->lastPage = $totalItems > 0 ? ceil($totalItems / $this->perPage) : 1;

        // Previous page
        $prevPage = $this->currentPage - 1;

        if($prevPage < 1)
        {
            $prevPage = $this->currentPage;
        }

        // Next page
        $nextPage = $this->currentPage + 1;

        if($nextPage > $this->lastPage)
        {
            $nextPage = $this->lastPage;
        }

        $this->feed->setCurrentPage($this->currentPage);
        $this->feed->setPrevPage($feedURL . '&page=' . $prevPage);
        $this->feed->setNextPage($feedURL . '&page=' . $nextPage);
        $this->feed->setLastPage($this->lastPage);

        echo $this->feed->getData();
    }

    /**
     * Validate request params
     */
    private function validateReqParams()
    {
        // Current page
        $currentPage = !empty($_GET['page']) ? (int)$_GET['page'] : 0;

        if($currentPage > 0)
        {
            $this->currentPage = $currentPage;
        }

        // Per page
        $perPage = !empty($_GET['per_page']) ? (int)$_GET['per_page'] : 0;

        if($perPage > 0 && $perPage <= 500)
        {
            $this->perPage = $perPage;
        }

        // Token
        $token = !empty($_GET['token']) ? $_GET['token'] : null;

        if(!empty($token))
        {
            $this->token = $token;
        }
    }

    /**
     * @param $categories
     * @return array
     */
    private function getCategoryBreadcrumbs($categories)
    {
        $breadcrumbs = [];

        foreach ($categories AS $category)
        {
            if (!empty($category['parents']))
            {
                $breadcrumbs = array_merge($breadcrumbs, $this->getCategoryBreadcrumbs($category['parents']));
            }

            $breadcrumbs[] = [
                'id'     => $category['category_id'],
                'name'   => $category['category'],
                'parent' => !empty($category['parent_id']) ? $category['parent_id'] : false
            ];
        }

        return $breadcrumbs;
    }
}

/**
 * Initialise feed
 */
$RTGFeeds = new RTGFeeds();

try
{
    switch ($mode)
    {
        case 'customers':
            $RTGFeeds->getCustomers();
            break;

        case 'products':
            $RTGFeeds->getProducts();
            break;

        default:
            echo 'Mode arg has wrong value!';
            break;
    }
}
catch (Exception $exception)
{
    echo '<pre>'; print_r($exception->getTraceAsString()); echo '</pre>';
}

exit(0);