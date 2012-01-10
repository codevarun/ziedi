<?php

/**
 * @property string $id
 * @property string $product_id
 * @property string $product_node_id
 * @property string $email
 * @property string $sent
 * @property string $created
 */
class Notifying extends CActiveRecord {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'notifyings';
    }

    public function rules() {
        return array(
            array('product_id, product_node_id, email', 'required'),
            array('product_id, product_node_id', 'length', 'max' => 11),
            array('email', 'length', 'max' => 250),
            array('sent, created', 'safe'),
            array('id, product_id, product_node_id, email, sent, created', 'safe', 'on' => 'search'),
        );
    }

    public function relations() {
        return array(
            'productNode' => array(self::BELONGS_TO, 'ProductNode', 'product_node_id'),
            'product' => array(self::BELONGS_TO, 'Product', 'product_id'),
        );
    }

    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'product_id' => 'Product',
            'product_node_id' => 'Product Node',
            'email' => 'Email',
            'sent' => 'Sent',
            'created' => 'Created',
        );
    }

    public function checkNotify($params) {
        return $this->countByAttributes(array(
            'product_id' => $params['productId'],
            'product_node_id' => $params['productNodeId'],
            'email' => $params['email']
        ));
    }

    public function findNotSent() {
        return $this->findAllByAttributes(array(
            'sent' => null
        ));
    }

}