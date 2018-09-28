<?php

function getCategories(){
    $categories=F('app_categories');
    if(!$categories){
        $categories=M('app_article_category')->where(array('status'=>1))->order('list_order asc')->select();
    }
    return $categories;
}