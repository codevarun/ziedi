<?php

class CategoryController extends Controller {

    public function actionIndex($id) {
        $category = Category::model()->getCategory($id);
        if (Yii::app()->request->isAjaxRequest) {
            if ($category) {
                if ($category->products) {
                    $content = '';
                    foreach ($category->products AS $categoryProduct) {
                        $product = $categoryProduct->getProduct();
                        $image = Attachment::model()->getAttachment('product', $product->id);
                        $imageLink = Yii::app()->params['images'] . $image->image;
                        $thumb = CHtml::link(CHtml::image(Image::thumb(Yii::app()->params['images'] . $image->image, 80), $product->content->title), 
                                CHtml::normalizeUrl(array('/'.$category->slug.'/'.$product->slug.'-'.$product->id)), array('class' => 'gift-image'));
                        $content .= '<div class="one"><div class="img">'.$thumb.'</div>'
                            .'<span>'.number_format($product->mainNode->price / $this->currencyValue,2,'.','').'</span>'
                            .'<span><a href="#" class="gift-item-order">Pasūtīt</a></span></div>';
                    }
                    echo $content;
                } else {
                    echo Yii::t('app', 'Products not found');
                }
            } else {
                echo 'false';
            }
            Yii::app()->end();
        }
        
        if (!$category) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        if ($category->parent_id > 1) {
            $parentCategories = array();
            $slugs = explode('/', $id);
            for ($i = 0; $i < (count($slugs) - 1); $i++) {
                $parentCategories[] = Category::model()->getCategory($slugs[$i]);
            }
            foreach ($parentCategories as $parentCategory) {
                $this->breadcrumbs[$parentCategory->content->title] = array(
                    '/' . $parentCategory->slug,
                );
            }
        }
        $this->breadcrumbs[] = $category->content->title;

        $this->metaTitle = $category->content->meta_title;
        $this->metaDescription = $category->content->meta_description;
        $this->metaKeywords = $category->content->meta_keywords;
        $this->background = $category->content->background;

        if ($category->childs) {
            $this->render('index', array(
                'category' => $category,
            ));
        } else {
            $products = array();
            if ($category->products) {
                foreach ($category->products AS $product) {
                    if ($product->active == 0)
                        continue;
                    if ($product->deleted == 1)
                        continue;
                    $productContent = $product->getProduct();
                    $products[] = $product;
                }
                $total = count($products);
                $limit = 6;
                $offset = 0;

                $page = (isset($_GET['page']) AND $_GET['page'] > 0) ? $_GET['page'] : 1;
                $pages = ceil($total / $limit);

                if ($page > 1) {
                    $offset = $limit * ($page - 1);
                }
                $nextpage = $page + 1;
                $prevpage = $page - 1;

                $session = new CHttpSession();
                $session->open();
                if (isset($_GET['page']))
                    $session->add('categoryPage', $_GET['page']);
                else
                    $session->remove('categoryPage');
                
                $this->render('products', array(
                    'category' => $category,
                    'products' => $products,
                    'total' => $total,
                    'page' => $page,
                    'pages' => $pages,
                    'offset' => $offset,
                    'limit' => $limit,
                    'nextpage' => $nextpage,
                    'prevpage' => $prevpage,
                ));
            } else {
                $this->render('no_products', array(
                    'category' => $category,
                ));
            }
        }
    }

}