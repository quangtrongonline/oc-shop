<?php namespace DShoreman\Shop\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use DShoreman\Shop\Models\Category as ShopCategory;
use DShoreman\Shop\Models\Product as ShopProduct;

class Products extends Product
{

    public function componentDetails()
    {
        return [
            'name'        => 'Shop Product List',
            'description' => 'Display products from a given category',
        ];
    }

    public function defineProperties()
    {
        return array_merge(parent::defineProperties(), [
            'categoryFilter' => [
                'title'       => 'Category filter',
                'description' => 'Enter a category slug or URL parameter to filter the posts by. Leave empty to show all posts.',
                'type'        => 'string',
                'default'     => ''
            ],
            'productPage' => [
                'title'       => 'Product Page',
                'description' => 'Name of the product page for the product titles. This property is used by the default component partial.',
                'type'        => 'dropdown',
                'default'     => 'shop/product',
                'group'       => 'Links',
            ],
            'productColumnClass' => [
                'title' => 'Product column',
                'group' => 'CSS Classes',
            ],
        ]);
    }

    public function getProductPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->prepareVars();

        if ($this->category) {
            $this->products = $this->page['products'] = $this->listProducts();
        }
    }

    public function prepareVars()
    {
        parent::prepareVars();

        $this->productPage = $this->page['productPage'] = $this->property('productPage');
        $this->category = $this->page['category'] = $this->loadCategory();

        $this->productColumnClass = $this->page['productColumnClass'] = $this->property('productColumnClass');
    }

    public function loadCategory()
    {
        if (!$categoryId = $this->propertyOrParam('categoryFilter'))
            return null;

        if (!$category = ShopCategory::whereSlug($categoryId))
            return null;

        return $category->first();
    }

    public function listProducts()
    {
        $products = ShopProduct::whereCategoryId($this->category->id)->get();

        $products->each(function($product)
        {
            $product->setUrl($this->productPage, $this->controller);
        });

        return $products;
    }

}
