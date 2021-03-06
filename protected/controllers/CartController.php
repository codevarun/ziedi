<?php

class CartController extends Controller {

    public function actionIndex() {
        $referer = "/";
        if ($_POST) {
            $cart = $this->cart->getList();
            if (!$cart)
                $cart = $this->cart->create();
            if ($_POST['action'] == 'changeNode') {
                $product = Product::model()->findByPk($_POST['productId']);
                $node = $product->getProduct($_POST['newProductNodeId']);
                $this->cart->changeNode($_POST['productId'], $_POST['productNodeId'], $_POST['newProductNodeId'], $node->mainNode->price);
            }
            if ($_POST['action'] == 'addItem') {
                $this->cart->addItem($_POST['productId'], $_POST['productNodeId'], $_POST['price'], $this->currency);
                $referer = (isset($_SERVER["HTTP_REFERER"])) ? preg_replace("/(\/[a-zA-Z0-9\-]+\-[0-9]+)/", "", $_SERVER["HTTP_REFERER"]) : '/';
            }
            if ($_POST['action'] == 'removeItem') {
                $this->cart->removeItem($_POST['productId'], $_POST['productNodeId']);
            }
            if ($_POST['action'] == 'changeQuantity') {
                if ($_POST['quantity'] <= 0)
                    $this->cart->removeItem($_POST['productId'], $_POST['productNodeId']);
                else
                    $this->cart->changeQuantity($_POST['productId'], $_POST['productNodeId'], $_POST['quantity']);
            }
            if ($_POST['action'] == 'addPhrase') {
                $this->cart->setPhrase($_POST['phrase_id'], $_POST['phrase'], $_POST['phrase_sign']);
            }

            if ($_POST['action'] == 'copy_from_wishlist') {
                foreach ($this->wishlistManager->getItems() AS $wishlistItem) {
                    $productNode = ProductNode::model()->findByPk($wishlistItem['product_node_id']);
                    if ($productNode) {
                        $this->cart->addItem($wishlistItem['product_id'], $wishlistItem['product_node_id'], $productNode->price);
                        $this->cart->changeQuantity($wishlistItem['product_id'], $wishlistItem['product_node_id'], $wishlistItem['quantity']);
                    }
                }
            }
            
            if ($_POST['action'] == 'updateCart') {
                foreach ($_POST['products'] AS $itemKey => $itemValue) {
                    if ($itemValue['quantity'] == 0)
                        $this->cart->removeItem($itemValue['productId'], $itemValue['productNodeId']);
                    else
                        $this->cart->changeQuantity($itemValue['productId'], $itemValue['productNodeId'], $itemValue['quantity']);
                }
                $this->cart->setAnonymousDelivery(isset($_POST['anonymous_delivery']));
                $this->cart->setFreeDeliveryPhoto(isset($_POST['free_delivery_photo']));
            }
        }

        $saleSum = 0;

        $cart = $this->cart->getList();
        $cartItems = array();
        foreach ($this->cart->getItems() AS $cartItem) {
            $product = Product::model()->findByPk($cartItem['product_id']);
            if (!$product) {
                continue;
            }
            $productNode = $product->getProduct($cartItem['product_node_id']);
            if ($productNode->mainNode->sale) {
                $saleSum += $productNode->mainNode->price;
            }
            $cartItems[] = array(
                'item' => $cartItem,
                'product' => $productNode
            );
        }

        $discount = 0;
        $discountType = '';
        $couponId = $this->cart->getCoupon();
        if ($couponId) {
            $coupon = Coupon::model()->getActiveCoupon($couponId);
            if ($coupon) {
                $discountType = ($coupon->percentage == 1) ? 'percentage' : 'value';
                $discount = $coupon->value;
                if ($coupon->not_for_sale != 1) {
                    $saleSum = 0;
                }
            }
        }

        $countries = array();
        $activeCountries = Country::model()->getActive();
        foreach ($activeCountries AS $activeCountry) {
            $countries[] = $activeCountry->title;
        }
        
        $giftRoot = Category::model()->getGiftParent();
        $gifts = $giftRoot->getListed('gifts');
        
        $postcardRoot = Category::model()->getPostcardParent();
        $postcards = $postcardRoot->getListed('postcard');
        
        $phraseRoot = Phrasecategory::model()->findByPk(1);
        $phrases = $phraseRoot->getListed('postcard');

        $total = array(
            'count' => $this->cart->getTotalCount(),
            'price' => $this->cart->getTotalPrice(),
        );
        $options = array(
            'anonymous_delivery' => $this->cart->getAnonymousDelivery(),
            'free_delivery_photo' => $this->cart->getFreeDeliveryPhoto(),
        );

        $this->breadcrumbs[] = Yii::t('app', 'Cart');
        $this->render('index', array(
            'list' => $cart,
            'items' => $cartItems,
            'cartItems' => $this->cart->getItems(),
            'countries' => $countries,
            'discount' => $discount,
            'saleSum' => $saleSum,
            'discountType' => $discountType,
            'referer' => $referer,
            'gifts' => $gifts,
            'postcards' => $postcards,
            'total' => $total,
            'options' => $options,
            'phrases' => $phrases,
        ));
    }

    public function actionCoupon() {
        if ($_POST) {
            $coupon = Coupon::model()->checkCode($_POST['code']);
            if ($coupon AND !$this->cart->getCoupon()) {
                $this->cart->setCoupon($coupon->id);
            }
        }
        Yii::app()->controller->redirect(array('/cart'));
    }

}