<?php
/**
 * Product Controller
 * Handles product list display and synchronization
 * 
 * @package    nicsrs_ssl_admin
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP (https://hvn.vn)
 */

namespace NicsrsAdmin\Controller;

use WHMCS\Database\Capsule;
use NicsrsAdmin\Service\NicsrsApiService;
use NicsrsAdmin\Service\ProductService;
use NicsrsAdmin\Helper\Pagination;

class ProductController extends BaseController
{
    /**
     * @var NicsrsApiService API service
     */
    private $apiService;
    
    /**
     * @var ProductService Product service
     */
    private $productService;

    /**
     * @var array Available vendors
     */
    private $vendors = [
        'Sectigo', 
        'DigiCert', 
        'GlobalSign', 
        'GeoTrust', 
        'Thawte',
        'RapidSSL',
        'sslTrus', 
        'Entrust',
        'BaiduTrust',
    ];

    /**
     * Constructor
     * 
     * @param array $vars Module variables
     */
    public function __construct(array $vars)
    {
        parent::__construct($vars);
        $this->apiService = new NicsrsApiService($this->getApiToken());
        $this->productService = new ProductService($this->apiService);
    }

    /**
     * Render products page
     * 
     * @param string $action Current action
     * @return void
     */
    public function render(string $action): void
    {
        $page = $this->getCurrentPage();
        $perPage = $this->getItemsPerPage();
        $vendor = isset($_GET['vendor']) ? $this->sanitize($_GET['vendor']) : '';
        $type = isset($_GET['type']) ? $this->sanitize($_GET['type']) : '';
        $search = isset($_GET['search']) ? $this->sanitize($_GET['search']) : '';

        // Build query
        $query = Capsule::table('mod_nicsrs_products');
        
        if ($vendor) {
            $query->where('vendor', $vendor);
        }
        if ($type) {
            $query->where('validation_type', $type);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        // Get total count
        $total = $query->count();

        // Get products
        $products = $query
            ->orderBy('vendor')
            ->orderBy('product_name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Process products
        $processedProducts = [];
        foreach ($products as $product) {
            $priceData = json_decode($product->price_data, true) ?: [];
            $processedProducts[] = [
                'id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'vendor' => $product->vendor,
                'validation_type' => $product->validation_type,
                'support_wildcard' => $product->support_wildcard,
                'support_san' => $product->support_san,
                'max_domains' => $product->max_domains,
                'max_years' => $product->max_years,
                'price_1y' => isset($priceData['basePrice']['price012']) ? $priceData['basePrice']['price012'] : null,
                'price_2y' => isset($priceData['basePrice']['price024']) ? $priceData['basePrice']['price024'] : null,
                'san_price' => isset($priceData['sanPrice']['price012']) ? $priceData['sanPrice']['price012'] : null,
                'last_sync' => $product->last_sync,
            ];
        }

        // Get unique vendors from database
        $availableVendors = Capsule::table('mod_nicsrs_products')
            ->distinct()
            ->orderBy('vendor')
            ->pluck('vendor')
            ->toArray();

        // Create pagination
        $pagination = new Pagination($total, $perPage, $page, $this->modulelink . '&action=products');

        // Get last sync time
        $lastSync = Capsule::table('mod_nicsrs_products')
            ->max('last_sync');

        $data = [
            'products' => $processedProducts,
            'vendors' => $this->vendors,
            'availableVendors' => $availableVendors,
            'currentVendor' => $vendor,
            'currentType' => $type,
            'search' => $search,
            'pagination' => $pagination,
            'total' => $total,
            'lastSync' => $lastSync,
        ];

        $this->includeTemplate('products/list', $data);
    }

    /**
     * Handle AJAX requests
     * 
     * @param array $post POST data
     * @return string JSON response
     */
    public function handleAjax(array $post): string
    {
        $action = isset($post['ajax_action']) ? $post['ajax_action'] : '';

        switch ($action) {
            case 'sync_vendor':
                $vendor = isset($post['vendor']) ? $this->sanitize($post['vendor']) : '';
                return $this->syncVendorProducts($vendor);

            case 'sync_all':
                return $this->syncAllProducts();

            case 'get_product_detail':
                $productId = isset($post['product_id']) ? (int) $post['product_id'] : 0;
                return $this->getProductDetail($productId);

            default:
                return $this->jsonError('Unknown action');
        }
    }

    /**
     * Sync products from specific vendor
     * 
     * @param string $vendor Vendor name
     * @return string JSON response
     */
    private function syncVendorProducts(string $vendor): string
    {
        if (empty($vendor)) {
            return $this->jsonError('Vendor is required');
        }

        try {
            $result = $this->productService->syncFromApi($vendor);
            
            // Log activity
            $this->logger->log('sync_products', 'product', null, null, json_encode([
                'vendor' => $vendor,
                'count' => $result['count'],
            ]));
            
            return $this->jsonSuccess(
                "Synced {$result['count']} products from {$vendor}",
                ['count' => $result['count']]
            );
        } catch (\Exception $e) {
            return $this->jsonError('Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync products from all vendors
     * 
     * @return string JSON response
     */
    private function syncAllProducts(): string
    {
        $totalCount = 0;
        $errors = [];
        $vendorResults = [];

        foreach ($this->vendors as $vendor) {
            try {
                $result = $this->productService->syncFromApi($vendor);
                $totalCount += $result['count'];
                $vendorResults[$vendor] = $result['count'];
            } catch (\Exception $e) {
                $errors[] = "{$vendor}: {$e->getMessage()}";
                $vendorResults[$vendor] = 0;
            }
        }

        // Log activity
        $this->logger->log('sync_all_products', 'product', null, null, json_encode([
            'total' => $totalCount,
            'vendors' => $vendorResults,
            'errors' => $errors,
        ]));

        if (empty($errors)) {
            return $this->jsonSuccess(
                "Successfully synced {$totalCount} products from all vendors",
                ['total' => $totalCount, 'vendors' => $vendorResults]
            );
        }

        return $this->jsonResponse([
            'success' => $totalCount > 0,
            'message' => "Synced {$totalCount} products with some errors",
            'total' => $totalCount,
            'vendors' => $vendorResults,
            'errors' => $errors,
        ]);
    }

    /**
     * Get product detail
     * 
     * @param int $productId Product ID
     * @return string JSON response
     */
    private function getProductDetail(int $productId): string
    {
        $product = Capsule::table('mod_nicsrs_products')
            ->where('id', $productId)
            ->first();

        if (!$product) {
            return $this->jsonError('Product not found');
        }

        $priceData = json_decode($product->price_data, true) ?: [];

        return $this->jsonSuccess('', [
            'product' => [
                'id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'vendor' => $product->vendor,
                'validation_type' => strtoupper($product->validation_type),
                'support_wildcard' => (bool) $product->support_wildcard,
                'support_san' => (bool) $product->support_san,
                'max_domains' => $product->max_domains,
                'max_years' => $product->max_years,
                'price_data' => $priceData,
                'last_sync' => $product->last_sync,
            ],
        ]);
    }
}