<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksey
 * Date: 22.03.12
 * Time: 14:53
 *
 */
class App_Form_NewItem extends Zend_Form
{

   public function init()
   {
       $name = new Zend_Form_Element_Text('name', array(
           'required' => true,
           'label' => 'Системное имя товара'
       ));

       $title = new Zend_Form_Element_Text('title', array(
           'required' => true,
           'label' => 'Наименование'
       ));

       $price = new Zend_Form_Element_Text('price', array(
           'required' => true,
           'label' => 'Цена'
       ));

       $submit = new Zend_Form_Element_Submit('submit');
       $submit->setName('Добавить');

       $this->setElements(array(
           'name' => $name,
           'title' => $title,
           'price' => $price,
           'submit' => $submit
       ));

       $this->setAction('shop/additem')
            ->setMethod('post');
   }

}
