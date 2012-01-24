<?php

/**
 * @property string $id
 * @property string $parent_id
 * @property integer $active
 * @property integer $sort
 * @property string $slug
 * @property string $image
 * @property string $created
 * @property Product[] $products
 */
class Category extends CActiveRecord {

    public $content;

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'categories';
    }

    public function rules() {
        return array(
            array('slug', 'required'),
            array('active, sort, gift, postcard', 'numerical', 'integerOnly' => true),
            array('parent_id', 'length', 'max' => 11),
            array('slug, image', 'length', 'max' => 250),
            array('created', 'safe'),
            array('id, parent_id, active, gift, postcard, sort, slug, image, created', 'safe', 'on' => 'search'),
        );
    }

    public function relations() {
        return array(
            'getparent' => array(self::BELONGS_TO, 'Category', 'parent_id'),
            'childs' => array(self::HAS_MANY, 'Category', 'parent_id', 'order' => 'sort ASC'),
            'products' => array(self::MANY_MANY, 'Product', 'product_category(category_id, product_id)', 'order' => 'sort ASC'),
        );
    }

    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'parent_id' => 'Parent',
            'active' => 'Active',
            'sort' => 'Sort',
            'slug' => 'Slug',
            'image' => 'Image',
            'created' => 'Created',
        );
    }
	
	public function scopes() {
        return array(
            'active' => array(
                'condition' => 'active = 1'
            ),
            'notDeleted' => array(
                'condition' => 'deleted = 0'
            ),
        );
    }

    public function getAllCategories() {
        return $this->notDeleted()->findAll("id > 1");
    }

    public function getCategory($slug) {
        $category = $this->notDeleted()->findByAttributes(array('slug' => $slug));
        if (!$category) {
            return false;
        }
        $category->content = Content::model()->getModuleContent('category', $category->id);
        return $category;
    }
    
    public function getCategorySlug($id) {
        $category = $this->notDeleted()->findByPk($id);
        return ($category) ? $category->slug : false;
    }
    
    public function getGiftParent() {
        return $this->notDeleted()->findByAttributes(array(
            'gift' => 1
        ));
    }
    
    public function getPostcardParent() {
        return $this->notDeleted()->findByAttributes(array(
            'postcard' => 1
        ));
    }

    public function getListed($id = '', $visibleAll = false) {
        $subitems = array();
        if ($this->childs)
            foreach ($this->childs as $child) {
                if ($child->active != 1 OR $child->deleted == 1)
                    continue;
                $subitems[] = $child->getListed($id, $visibleAll);
            }
        $categoryContent = Content::model()->getModuleContent('category', $this->id);
        $active = (preg_match("/" . str_replace("/", "\/", $this->slug) . "/", $id) > 0);
        $returnarray = array(
            'label' => (isset($categoryContent->title)) ? $categoryContent->title : '',
            'url' => ($this->childs) ? '#' : array('/' . $this->slug),
            'active' => $active
        );
        if ($subitems != array())
            $returnarray = array_merge($returnarray, array('items' => $subitems));
        return $returnarray;
    }

    public function getTableRows($level = 0) {
        $subitems = array();
        $returnRows = array();
        if ($this->id != 1) {
            $level = $level + 1;
        }
        if ($this->childs)
            foreach ($this->childs as $child) {
				if ($child->deleted == 1)
                    continue;
                $subitems[] = $child->getTableRows($level);
            }
        if ($this->id != 1) {
            $content = Content::model()->getModuleContent('category', $this->id);
            $returnRows = array(
                'level' => $level,
                'controller' => 'category',
                'id' => $this->id,
                'slug' => $this->slug,
                'linkTitle' => $content->title,
                'active' => $this->active,
                'created' => $this->created,
            );
        }

        if ($subitems != '')
            $returnRows = array_merge($returnRows, array('items' => $subitems));
        return $returnRows;
    }

    public function getOptionList($parent = '') {
        $subitems = array();
        $categoryContent = Content::model()->getModuleContent('category', $this->id);
        $title = (isset($categoryContent->title)) ? $categoryContent->title : '';
        if ($this->childs)
            foreach ($this->childs as $child) {
				if ($child->deleted == 1)
                    continue;
                $subitems[] = $child->getOptionList($title);
            }
        if ($this->id > 1) {
            $returnArray[$this->id . ' '] = ($parent ? $parent . ' > ' : '') . $categoryContent->title;
        } else {
            $returnArray = array();
        }
        if ($subitems != array())
            foreach ($subitems AS $subitem) {
                $returnArray = array_merge($returnArray, $subitem);
            }
        return $returnArray;
    }

}