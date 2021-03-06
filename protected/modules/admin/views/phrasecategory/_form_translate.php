<?php 
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'category-form',
    'enableAjaxValidation' => false,
));
echo $form->errorSummary(array($categoryModel, $contentModel)); 
?>
<p>
    <?php echo $form->labelEx($categoryModel, 'parent_id'); ?>
    <span class="note error"><?php echo $form->error($categoryModel, 'parent_id'); ?></span><br/>
    <select name="Phrasecategory[parent_id]" id="Phrasecategory_parent_id" class="styled big" disabled="disabled">
        <option value="1">---</option>
        <?php 
        $rendered = new Renderer();
        $rendered->renderRecursive($categories['items'], $categoryModel->parent_id, Renderer::RENDER_OPTION_LIST);
        ?>
    </select>
</p>
<p>
    <?php echo $form->labelEx($contentModel, 'title'); ?><br/>
    <?php echo $form->textField($contentModel,'title', array('class' => 'text medium')); ?>
    <span class="note error"><?php echo $form->error($contentModel, 'title'); ?></span>
</p>
<p>
    <?php echo $form->labelEx($categoryModel, 'slug'); ?><br/>
    <?php echo $form->textField($categoryModel,'slug', array('class' => 'text medium', 'disabled' => 'disabled')); ?>
    <span class="note error"><?php echo $form->error($categoryModel, 'slug'); ?></span>
</p>
<p>
    <?php echo $form->labelEx($contentModel, 'language'); ?><br/>
    <?php echo $form->dropDownList($contentModel,'language', $this->languages, array('class' => 'styled small', 'disabled' => 'disabled')); ?>
</p>
<p>
    <?php echo $form->labelEx($categoryModel, 'sort'); ?><br/>
    <?php echo $form->textField($categoryModel,'sort', array('class' => 'text tiny', 'disabled' => 'disabled')); ?>
    <span class="note error"><?php echo $form->error($categoryModel, 'sort'); ?></span>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
    <?php echo $form->checkBox($categoryModel, 'active', array('class' => 'checkbox', 'disabled' => 'disabled')); ?>
    <?php echo $form->labelEx($categoryModel, 'active'); ?>
    <span class="note error"><?php echo $form->error($categoryModel, 'active'); ?></span>
</p>
<hr/>
<p>
    <?php echo CHtml::submitButton($categoryModel->isNewRecord ? 'Create' : 'Save', array('id' => 'submit', 'class' => 'submit small')); ?>
</p>
<?php $this->endWidget(); ?>